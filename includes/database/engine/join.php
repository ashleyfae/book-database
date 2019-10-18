<?php
/**
 * Join
 *
 * Perform simple joins with one other table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\BerlinDB\Database\Queries;

use Book_Database\BerlinDB\Database\Base;
use function Book_Database\book_database;

/**
 * Class Join
 * @package Book_Database\BerlinDB\Database\Queries
 */
abstract class Join extends Base {

	/**
	 * Name of the joined table key
	 *
	 * @var string
	 */
	protected $joined_table_key = '';

	/**
	 * Name of the editions table
	 *
	 * @var string
	 */
	protected $joined_table_name;

	/**
	 * Alias to use in queries
	 *
	 * @var string
	 */
	protected $joined_table_alias = '';

	/**
	 * Column name in the editions table to match against the other table.
	 *
	 * @var string
	 */
	protected $joined_table_column = '';

	/**
	 * Name of the primary table we're joining with
	 *
	 * @var string
	 */
	protected $primary_table_name;

	/**
	 * Name of the primary column name we're joining on
	 *
	 * @var string
	 */
	protected $primary_column;

	/**
	 * Query arguments
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Column whitelist
	 *
	 * @var array
	 */
	protected $columns_whitelist = array();

	/**
	 * Standard response when the query should not return any rows.
	 *
	 * @var array
	 */
	private static $no_results = array(
		'join'  => '',
		'where' => '0 = 1'
	);

	/**
	 * Edition constructor.
	 *
	 * @param array  $args               {
	 *                                   Query arguments.
	 *
	 * @type array {
	 * @type string  $field              Column name to search against.
	 * @type mixed   $value              Value to check.
	 * @type string  $operator           MySQL operator. Accepts '=', '!=', 'LIKE
	 *             }
	 * }
	 *
	 * @param string $primary_table_name Name of the table to join with.
	 * @param string $primary_column     Name of the column to join on.
	 */
	public function __construct( $args = array(), $primary_table_name = '', $primary_column = '' ) {

		$this->primary_table_name = $primary_table_name;
		$this->primary_column     = $primary_column;
		$this->args               = $args;

		$this->set_joined_table();

	}

	/**
	 * Set the name of the table we're joining on (this table)
	 */
	protected function set_joined_table() {
		$table = book_database()->get_table( $this->joined_table_key );

		if ( empty( $table ) ) {
			return;
		}

		$this->joined_table_name = $table->get_table_name();
		$this->joined_table_alias ?? $this->joined_table_name;
	}

	/**
	 * Get SQL to use in queries
	 *
	 * @return array
	 */
	public function get_sql() {

		if ( empty( $this->joined_table_name ) || empty( $this->joined_table_alias ) || empty( $this->joined_table_column ) ) {
			return self::$no_results;
		}

		$sql = array(
			'join'  => '',
			'where' => ''
		);

		$sql['join']  = " INNER JOIN {$this->joined_table_name} {$this->joined_table_alias} ON {$this->joined_table_alias}.{$this->joined_table_column} = {$this->primary_table_name}.{$this->primary_column} ";
		$sql['where'] = $this->parse_where();

		return $sql;

	}

	/**
	 * Parse the `where` clauses
	 *
	 * @return string
	 */
	protected function parse_where() {

		$where = array();
		$and   = '/^\s*AND\s*/';

		foreach ( $this->args as $condition ) {
			if ( empty( $condition['field'] ) || ! array_key_exists( 'value', $condition ) ) {
				$where[] = self::$no_results;

				continue;
			}

			$column = $this->sanitize_column( $condition['field'] );

			if ( empty( $column ) ) {
				$where[] = self::$no_results;

				continue;
			}

			$operator    = $this->sanitize_operator( $condition['operator'] ?? '=' );
			$placeholder = is_int( $condition['value'] ) ? '%d' : '%s';
			$value       = 'LIKE' === $operator ? '%' . $this->get_db()->esc_like( $condition['value'] ) . '%' : $condition['value'];

			if ( is_null( $value ) ) {
				// If the value is null, then we check that directly.
				$where[] = "{$column} {$operator} NULL";
			} elseif ( false !== strpos( $column, 'date' ) ) {
				// Perform a date query if the column name contains "date".
				$date_query = new Date( $value, $column );
				$date_sql   = $date_query->get_sql();

				// Remove "AND".
				$where[] = preg_replace( $and, '', $date_sql );
			} else {
				// Otherwise, properly prepare.
				$where[] = $this->get_db()->prepare( " {$column} {$operator} {$placeholder} ", $value );
			}
		}

		return ! empty( $where ) ? implode( " AND ", $where ) : '';

	}

	/**
	 * Sanitize the operator
	 *
	 * @param string $operator
	 *
	 * @return string
	 */
	protected function sanitize_operator( $operator ) {
		$allowed = array(
			'=',
			'!=',
			'>',
			'>=',
			'<',
			'<=',
			'LIKE'
		);

		$operator = strtoupper( $operator );

		return in_array( $operator, $allowed ) ? $operator : '=';
	}

	/**
	 * Sanitize a column name
	 *
	 * @param string $column_name
	 *
	 * @return bool
	 */
	protected function sanitize_column( $column_name ) {

		$column_name = preg_replace( '/[^a-zA-Z0-9_$\.]/', '', $column_name );

		return in_array( $column_name, $this->columns_whitelist ) ? $column_name : false;

	}

}