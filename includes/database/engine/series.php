<?php
/**
 * Base Custom Database Table Tax Query Class.
 *
 * Taken from \WP_Tax_Query
 * @see \WP_Tax_Query
 *
 * @package     Database
 * @subpackage  Tax
 * @copyright   Copyright (c) 2019
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace Book_Database\BerlinDB\Database\Queries;

use Book_Database\BerlinDB\Database\Base;
use function Book_Database\book_database;
use function Book_Database\get_book_series;

/**
 * Core class used to implement series queries.
 *
 * Used for generating SQL clauses that filter a primary query according to object
 * taxonomy terms.
 *
 * Tax is a helper that allows primary query classes, such as \Book_Database\Books_Query, to filter
 * their results by object metadata, by generating `JOIN` and `WHERE` subclauses to be
 * attached to the primary SQL query string.
 */
class Series extends Base {

	/**
	 * Series table name
	 *
	 * @var string
	 */
	protected $series_table_name;

	/**
	 * Array of series queries.
	 *
	 * See Tax::__construct() for information on tax query arguments.
	 *
	 * @var array
	 */
	public $queries = array();

	/**
	 * The relation between the queries. Can be one of 'AND' or 'OR'.
	 *
	 * @var string
	 */
	public $relation;

	/**
	 * Standard response when the query should not return any rows.
	 *
	 * @var array
	 */
	private static $no_results = array(
		'join'  => array( '' ),
		'where' => array( '0 = 1' ),
	);

	/**
	 * A flat list of table aliases used in the JOIN clauses.
	 *
	 * @var array
	 */
	protected $table_aliases = array();

	/**
	 * Series fetched by this query.
	 *
	 * We store this data in a flat array because they are referenced in a
	 * number of places by WP_Query.
	 *
	 * @var array
	 */
	public $queried_terms = array();

	/**
	 * Database table that where the metadata's objects are stored (eg $wpdb->users).
	 *
	 * @var string
	 */
	public $primary_table;

	/**
	 * Column in 'primary_table' that represents the ID of the object.
	 *
	 * @var string
	 */
	public $primary_id_column;

	/**
	 * Constructor.
	 *
	 * @param array $series_query {
	 *     Array of series query clauses.
	 *
	 *     @type string $relation Optional. The MySQL keyword used to join
	 *                            the clauses of the query. Accepts 'AND', or 'OR'. Default 'AND'.
	 *     @type array {
	 *         Optional. An array of first-order clause parameters, or another fully-formed series query.
	 *
	 *         @type string|int|array $series           Series to filter by.
	 *         @type string           $field            Field to match $terms against. Accepts 'id', 'slug',
	 *                                                  'name', or `search`. Default: 'id'.
	 *         @type string           $operator         MySQL operator to be used with $series in the WHERE clause.
	 *                                                  Accepts 'IN', 'NOT IN'.
	 *                                                  Default: 'IN'.
	 *     }
	 * }
	 */
	public function __construct( $series_query ) {
		$this->set_tables();

		if ( isset( $series_query['relation'] ) ) {
			$this->relation = $this->sanitize_relation( $series_query['relation'] );
		} else {
			$this->relation = 'AND';
		}

		$this->queries = $this->sanitize_query( $series_query );
	}

	/**
	 * Set tables used in queries
	 */
	protected function set_tables() {
		$this->series_table_name = book_database()->get_table( 'series' )->get_table_name();
	}

	/**
	 * Ensure the 'tax_query' argument passed to the class constructor is well-formed.
	 *
	 * Ensures that each query-level clause has a 'relation' key, and that
	 * each first-order clause contains all the necessary keys from `$defaults`.
	 *
	 * @param array $queries Array of queries clauses.
	 * @return array Sanitized array of query clauses.
	 */
	public function sanitize_query( $queries ) {
		$cleaned_query = array();

		$defaults = array(
			'series'           => array(),
			'field'            => 'id',
			'operator'         => 'IN',
		);

		foreach ( $queries as $key => $query ) {
			if ( 'relation' === $key ) {
				$cleaned_query['relation'] = $this->sanitize_relation( $query );

				// First-order clause.
			} elseif ( self::is_first_order_clause( $query ) ) {

				$cleaned_clause           = array_merge( $defaults, $query );
				$cleaned_clause['series'] = (array) $cleaned_clause['series'];
				$cleaned_query[]          = $cleaned_clause;

			} elseif ( is_array( $query ) ) {
				$cleaned_subquery = $this->sanitize_query( $query );

				if ( ! empty( $cleaned_subquery ) ) {
					// All queries with children must have a relation.
					if ( ! isset( $cleaned_subquery['relation'] ) ) {
						$cleaned_subquery['relation'] = 'AND';
					}

					$cleaned_query[] = $cleaned_subquery;
				}
			}
		}

		return $cleaned_query;
	}

	/**
	 * Sanitize a 'relation' operator.
	 *
	 * @param string $relation Raw relation key from the query argument.
	 * @return string Sanitized relation ('AND' or 'OR').
	 */
	public function sanitize_relation( $relation ) {
		if ( 'OR' === strtoupper( $relation ) ) {
			return 'OR';
		} else {
			return 'AND';
		}
	}

	/**
	 * Determine whether a clause is first-order.
	 *
	 * A "first-order" clause is one that contains any of the first-order
	 * clause keys ('terms', 'taxonomy', 'field', 'operator'). An empty
	 * clause also counts as a first-order clause, for backward
	 * compatibility. Any clause that doesn't meet this is determined,
	 * by process of elimination, to be a higher-order query.
	 *
	 * @param array $query Tax query arguments.
	 *
	 * @return bool Whether the query clause is a first-order clause.
	 */
	protected static function is_first_order_clause( $query ) {
		return is_array( $query ) && ( empty( $query ) || array_key_exists( 'series', $query ) || array_key_exists( 'field', $query ) || array_key_exists( 'operator', $query ) );
	}

	/**
	 * Generates SQL clauses to be appended to a main query.
	 *
	 * @param string $primary_table     Database table where the object being filtered is stored (eg wp_users).
	 * @param string $primary_id_column ID column for the filtered object in $primary_table.
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql( $primary_table, $primary_id_column ) {
		$this->primary_table     = $primary_table;
		$this->primary_id_column = $primary_id_column;

		return $this->get_sql_clauses();
	}

	/**
	 * Generate SQL clauses to be appended to a main query.
	 *
	 * Called by the public WP_Tax_Query::get_sql(), this method
	 * is abstracted out to maintain parity with the other Query classes.
	 *
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to the main query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	protected function get_sql_clauses() {
		/*
		 * $queries are passed by reference to get_sql_for_query() for recursion.
		 * To keep $this->queries unaltered, pass a copy.
		 */
		$queries = $this->queries;
		$sql     = $this->get_sql_for_query( $queries );

		if ( ! empty( $sql['where'] ) ) {
			$sql['where'] = ' AND ' . $sql['where'];
		}

		return $sql;
	}

	/**
	 * Generate SQL clauses for a single query array.
	 *
	 * If nested subqueries are found, this method recurses the tree to
	 * produce the properly nested SQL.
	 *
	 * @param array $query Query to parse (passed by reference).
	 * @param int   $depth Optional. Number of tree levels deep we currently are.
	 *                     Used to calculate indentation. Default 0.
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to a single query array.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	protected function get_sql_for_query( &$query, $depth = 0 ) {
		$sql_chunks = array(
			'join'  => array(),
			'where' => array(),
		);

		$sql = array(
			'join'  => '',
			'where' => '',
		);

		$indent = '';
		for ( $i = 0; $i < $depth; $i++ ) {
			$indent .= '  ';
		}

		foreach ( $query as $key => &$clause ) {
			if ( 'relation' === $key ) {
				$relation = $query['relation'];
			} elseif ( is_array( $clause ) ) {

				// This is a first-order clause.
				if ( $this->is_first_order_clause( $clause ) ) {
					$clause_sql = $this->get_sql_for_clause( $clause, $query );

					$where_count = count( $clause_sql['where'] );
					if ( ! $where_count ) {
						$sql_chunks['where'][] = '';
					} elseif ( 1 === $where_count ) {
						$sql_chunks['where'][] = $clause_sql['where'][0];
					} else {
						$sql_chunks['where'][] = '( ' . implode( ' AND ', $clause_sql['where'] ) . ' )';
					}

					$sql_chunks['join'] = array_merge( $sql_chunks['join'], $clause_sql['join'] );
					// This is a subquery, so we recurse.
				} else {
					$clause_sql = $this->get_sql_for_query( $clause, $depth + 1 );

					$sql_chunks['where'][] = $clause_sql['where'];
					$sql_chunks['join'][]  = $clause_sql['join'];
				}
			}
		}

		// Filter to remove empties.
		$sql_chunks['join']  = array_filter( $sql_chunks['join'] );
		$sql_chunks['where'] = array_filter( $sql_chunks['where'] );

		if ( empty( $relation ) ) {
			$relation = 'AND';
		}

		// Filter duplicate JOIN clauses and combine into a single string.
		if ( ! empty( $sql_chunks['join'] ) ) {
			$sql['join'] = implode( ' ', array_unique( $sql_chunks['join'] ) );
		}

		// Generate a single WHERE clause with proper brackets and indentation.
		if ( ! empty( $sql_chunks['where'] ) ) {
			$sql['where'] = '( ' . "\n  " . $indent . implode( ' ' . "\n  " . $indent . $relation . ' ' . "\n  " . $indent, $sql_chunks['where'] ) . "\n" . $indent . ')';
		}

		return $sql;
	}

	/**
	 * Generate SQL JOIN and WHERE clauses for a "first-order" query clause.
	 *
	 * @global \wpdb $wpdb The WordPress database abstraction object.
	 *
	 * @param array $clause       Query clause (passed by reference).
	 * @param array $parent_query Parent query array.
	 * @return array {
	 *     Array containing JOIN and WHERE SQL clauses to append to a first-order query.
	 *
	 *     @type string $join  SQL fragment to append to the main JOIN clause.
	 *     @type string $where SQL fragment to append to the main WHERE clause.
	 * }
	 */
	public function get_sql_for_clause( &$clause, $parent_query ) {
		global $wpdb;

		$sql = array(
			'where' => array(),
			'join'  => array(),
		);

		$join = $where = '';

		$this->clean_query( $clause );

		if ( is_wp_error( $clause ) ) {
			return self::$no_results;
		}

		$terms    = $clause['series'];
		$operator = strtoupper( $clause['operator'] );

		if ( in_array( $operator, array( 'IN', 'NOT IN' ) ) ) {

			if ( empty( $terms ) ) {
				return self::$no_results;
			}

			$terms = implode( ',', $terms );

			$where = "{$this->primary_table}.{$this->primary_id_column} $operator ($terms)";

		} elseif ( 'NOT EXISTS' === $operator || 'EXISTS' === $operator ) {

			$where ="$operator (
				SELECT 1
				FROM {$this->series_table_name}
				WHERE {$this->series_table_name}.id = $this->primary_table.series_id
			)";

		}

		$sql['join'][]  = $join;
		$sql['where'][] = $where;
		return $sql;
	}

	/**
	 * Identify an existing table alias that is compatible with the current query clause.
	 *
	 * We avoid unnecessary table joins by allowing each clause to look for
	 * an existing table alias that is compatible with the query that it
	 * needs to perform.
	 *
	 * An existing alias is compatible if (a) it is a sibling of `$clause`
	 * (ie, it's under the scope of the same relation), and (b) the combination
	 * of operator and relation between the clauses allows for a shared table
	 * join. In the case of WP_Tax_Query, this only applies to 'IN'
	 * clauses that are connected by the relation 'OR'.
	 *
	 * @param array       $clause       Query clause.
	 * @param array       $parent_query Parent query of $clause.
	 * @return string|false Table alias if found, otherwise false.
	 */
	protected function find_compatible_table_alias( $clause, $parent_query ) {
		$alias = false;

		// Sanity check. Only IN queries use the JOIN syntax .
		if ( ! isset( $clause['operator'] ) || 'IN' !== $clause['operator'] ) {
			return $alias;
		}

		// Since we're only checking IN queries, we're only concerned with OR relations.
		if ( ! isset( $parent_query['relation'] ) || 'OR' !== $parent_query['relation'] ) {
			return $alias;
		}

		$compatible_operators = array( 'IN' );

		foreach ( $parent_query as $sibling ) {
			if ( ! is_array( $sibling ) || ! $this->is_first_order_clause( $sibling ) ) {
				continue;
			}

			if ( empty( $sibling['alias'] ) || empty( $sibling['operator'] ) ) {
				continue;
			}

			// The sibling must both have compatible operator to share its alias.
			if ( in_array( strtoupper( $sibling['operator'] ), $compatible_operators ) ) {
				$alias = $sibling['alias'];
				break;
			}
		}

		return $alias;
	}

	/**
	 * Validates a single query.
	 *
	 * @param array $query The single query. Passed by reference.
	 */
	protected function clean_query( &$query ) {
		$query['series'] = array_unique( (array) $query['series'] );

		$this->transform_query( $query, 'id' );
	}

	/**
	 * Transforms a single query, from one field to another.
	 *
	 * Operates on the `$query` object by reference. In the case of error,
	 * `$query` is converted to a WP_Error object.
	 *
	 * @global \wpdb $wpdb The WordPress database abstraction object.
	 *
	 * @param array  $query           The single query. Passed by reference.
	 * @param string $resulting_field The resulting field. Accepts 'slug', 'name', or 'id'.
	 *                                Default 'id'.
	 */
	public function transform_query( &$query, $resulting_field ) {
		if ( empty( $query['series'] ) ) {
			return;
		}

		if ( $query['field'] == $resulting_field ) {
			return;
		}

		$resulting_field = sanitize_key( $resulting_field );

		// Empty 'terms' always results in a null transformation.
		$terms = array_filter( $query['series'] );
		if ( empty( $terms ) ) {
			$query['series'] = array();
			$query['field'] = $resulting_field;
			return;
		}

		$args = array(
			'number'                 => 99999,
			'orderby'                => 'none',
			'fields'                 => $resulting_field
		);

		// Term query parameter name depends on the 'field' being searched on.
		switch ( $query['field'] ) {
			case 'slug':
				if ( is_array( $terms ) ) {
					$args['slug__in'] = $terms;
				} else {
					$args['slug'] = $terms;
				}
				break;
			case 'name':
				if ( is_array( $terms ) ) {
					$args['name__in'] = $terms;
				} else {
					$args['name'] = $terms;
				}
				break;
			case 'search' :
				$args['search'] = is_array( $terms ) ? implode( ' ', $terms ) : $terms;
				break;
			default:
				$args['id__in'] = wp_parse_id_list( $terms );
				break;
		}

		$query['series'] = get_book_series( $args );
	}
}
