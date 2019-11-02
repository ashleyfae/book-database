<?php
/**
 * Where Clause
 *
 * Helper class for building WHERE clauses.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\BerlinDB\Database\Queries\Date;

/**
 * Class Where_Clause
 * @package Book_Database
 */
class Where_Clause {

	protected $args = array();

	/**
	 * @var BerlinDB\Database\Query
	 */
	protected $query_class;

	/**
	 * Table column prefix
	 *
	 * @var string
	 */
	protected $column_prefix = '';

	/**
	 * Standard response when the query should not return any rows.
	 *
	 * @var array
	 */
	private static $no_results = '0 = 1';

	/**
	 * Whitelist of columns
	 *
	 * @var array
	 */
	protected $columns_whitelist = array();

	/**
	 * Where_Clause constructor.
	 *
	 * @param array $args     {
	 *                        Query arguments.
	 *                        Note this is an array of arrays.
	 *
	 *                        {
	 *
	 * @type string $field    Name of the column. If it contains `date` then $value is treated as a date query.
	 * @type mixed  $value    Value to query for.
	 * @type string $operator Query operator. Default `=`.
	 *
	 *   }
	 * }
	 */
	public function __construct( $args = array() ) {
		$this->set_args( $args );
	}

	/**
	 * @param $args
	 */
	public function set_args( $args ) {
		$this->args = $args;
	}

	/**
	 * Query class
	 *
	 * This is where we get the table alias for the column names
	 *
	 * @param BerlinDB\Database\Query $query
	 */
	public function set_table_query( $query ) {
		$this->query_class       = $query;
		$this->column_prefix     = $query->get_table_alias() . '.';
		$this->columns_whitelist = array_flip( $this->query_class->get_column_names() );
	}

	/**
	 * Get the clauses
	 *
	 * @return array
	 */
	public function get_clauses() {

		global $wpdb;

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

			$column      = $this->column_prefix . $column;
			$operator    = $this->sanitize_operator( $condition['operator'] ?? '=' );
			$type        = ! empty( $condition['type'] ) ? strtoupper( $condition['type'] ) : 'TEXT';
			$placeholder = is_int( $condition['value'] ) ? '%d' : '%s';
			$value       = 'LIKE' === $operator ? '%' . $wpdb->esc_like( $condition['value'] ) . '%' : $condition['value'];

			if ( in_array( $operator, array( 'IN', 'NOT IN' ) ) ) {

				$values             = is_array( $value ) ? $value : array( $value );
				$value_placeholder  = 'NUMERIC' === $type ? '%d' : '%s';
				$value_placeholders = array_fill( 0, count( $values ), $value_placeholder );
				$placeholder_string = implode( ', ', $value_placeholders );

				if ( 'NUMERIC' === $type ) {
					$sanitized_values = array_map( 'absint', $values );
				} else {
					$sanitized_values = array_map( 'wp_strip_all_tags', $values );
				}

				$where[] = $wpdb->prepare( "{$column} {$operator} ( {$placeholder_string} )", $sanitized_values );

			} else {

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
					$where[] = $wpdb->prepare( " {$column} {$operator} {$placeholder} ", $value );
				}

			}
		}

		return $where;

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
			'LIKE',
			'IN',
			'NOT IN',
			'IS',
			'IS NOT'
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