<?php

/**
 * Book Term Relationships DB Class
 *
 * This class is for interacting with the book term relationships database table.
 * Used for mapping relationships between terms and books
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Book_Term_Relationships extends BDB_DB {

	/**
	 * BDB_DB_Book_Term_Relationships constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'bdb_book_term_relationships';
		$this->primary_key = 'ID';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'ID'      => '%d',
			'term_id' => '%d',
			'book_id' => '%d'
		);
	}

	/**
	 * Get default column values.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'term_id' => 0,
			'book_id' => 0
		);
	}

	/**
	 * Add a Relationship
	 *
	 * @param array $data Relationship data.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int Relationship ID.
	 */
	public function add( $data = array() ) {

		$defaults = array();

		$args = wp_parse_args( $data, $defaults );

		$relationship = ( array_key_exists( 'ID', $args ) ) ? $this->get_relationship_by( 'ID', $args['ID'] ) : false;

		if ( $relationship ) {

			// Updating an existing relationship.
			$this->update( $relationship->ID, $args );

			return $relationship->ID;

		} else {

			// Adding a new relationship.
			return $this->insert( $args, 'relationship' );

		}

	}

	/**
	 * Delete a Relationship
	 *
	 * @param bool $id ID of the relationship to delete.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|int False on failure.
	 */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$relationship = $this->get_relationship_by( 'ID', $id );

		if ( $relationship->ID > 0 ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'ID' => $relationship->ID ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Retrieves a single relationship from the database.
	 *
	 * @param string $field The column to search.
	 * @param int    $value The value to check against the column.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object|false Upon success, an object of the term. Upon failure, false.
	 */
	public function get_relationship_by( $field = 'ID', $value = 0 ) {

		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( $field == 'ID' ) {
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {

			case 'ID' :
				$db_field = 'ID';
				break;

			default :
				return false;

		}

		if ( ! $relationship = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			return false;

		}

		return $relationship;

	}

	/**
	 * Retrieve relationships from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of objects.
	 */
	public function get_relationships( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'      => false,
			'number'  => 20,
			'offset'  => 0,
			'term_id' => false,
			'book_id' => false,
			'orderby' => 'ID',
			'order'   => 'DESC'
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific relationships.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Specific terms.
		if ( ! empty( $args['term_id'] ) ) {
			if ( is_array( $args['term_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['term_id'] ) );
			} else {
				$ids = intval( $args['term_id'] );
			}
			$where .= " AND `term_id` IN( {$ids} ) ";
		}

		// Specific books.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
		}

		$orderby = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'term_id' : wp_strip_all_tags( $args['orderby'] );
		$order   = ( 'ASC' == strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		$cache_key = md5( 'bdb_book_term_relationships_' . serialize( $args ) );

		$relationships = wp_cache_get( $cache_key, 'book_term_relationships' );

		if ( $relationships === false ) {
			$query         = $wpdb->prepare( "SELECT * FROM  $this->table_name $join $where GROUP BY $this->primary_key ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$relationships = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $relationships, 'book_term_relationships', 3600 );
		}

		return $relationships;

	}

	/**
	 * Relationship Count
	 *
	 * @param array $args
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'      => false,
			'offset'  => 0,
			'term_id' => false,
			'book_id' => false
		);

		$args = wp_parse_args( $args, $defaults );

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific relationships.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Specific terms.
		if ( ! empty( $args['term_id'] ) ) {
			if ( is_array( $args['term_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['term_id'] ) );
			} else {
				$ids = intval( $args['term_id'] );
			}
			$where .= " AND `term_id` IN( {$ids} ) ";
		}

		// Specific books.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
		}

		$cache_key = md5( 'bdb_book_term_relationships_count_' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'book_term_relationships' );

		if ( $count === false ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'book_term_relationships', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		term_id bigint(20) NOT NULL,
		book_id bigint(20) NOT NULL,
		PRIMARY KEY  (ID),
		INDEX term_id (term_id),
		INDEX book_id (book_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

}