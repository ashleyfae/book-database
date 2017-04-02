<?php

/**
 * Series DB Class
 *
 * This class is for interacting with the series database table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Series extends BDB_DB {

	/**
	 * BDB_DB_Series constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'bdb_series';
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
			'ID'          => '%d',
			'name'        => '%s',
			'slug'        => '%s',
			'description' => '%s'
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
			'name'        => '',
			'slug'        => '',
			'description' => ''
		);
	}

	/**
	 * Add a Series
	 *
	 * @param array $data Series data.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int Book ID.
	 */
	public function add( $data = array() ) {

		$defaults = array();

		$args = wp_parse_args( $data, $defaults );

		if ( ! array_key_exists( 'slug', $args ) && array_key_exists( 'name', $args ) ) {
			$slug         = sanitize_title( $args['name'] );
			$args['slug'] = bdb_unique_slug( $slug, 'series' );
		}

		$series = ( array_key_exists( 'ID', $args ) ) ? $this->get_series_by( 'ID', $args['ID'] ) : false;

		if ( $series ) {

			// Updating an existing book.
			$this->update( $series->ID, $args );

			return $series->ID;

		} else {

			// Adding a new book.
			return $this->insert( $args, 'series' );

		}

	}

	/**
	 * Delete a Series.
	 *
	 * @param bool $id ID of the series to delete.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|int False on failure.
	 */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$series = $this->get_series_by( 'ID', $id );

		if ( $series->ID > 0 ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'ID' => $series->ID ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Check if a series exists.
	 *
	 * @param string $value Value of the column.
	 * @param string $field Which field to check.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function exists( $value = '', $field = 'ID' ) {

		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'ID', $field, $value );

	}

	/**
	 * Retrieves a single series from the database.
	 *
	 * @param string $field The column to search.
	 * @param int    $value The value to check against the column.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object|false Upon success, an object of the series. Upon failure, false.
	 */
	public function get_series_by( $field = 'ID', $value = 0 ) {

		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( 'ID' == $field ) {
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}
		} elseif ( 'slug' == $field ) {
			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {

			case 'ID' :
				$db_field = 'ID';
				break;

			case 'name' :
				$db_field = 'name';
				$value    = wp_strip_all_tags( $value );
				break;

			case 'slug' :
				$value    = sanitize_text_field( $value );
				$db_field = 'slug';
				break;

			default :
				return false;

		}

		if ( ! $series = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			return false;

		}

		return $series;

	}

	/**
	 * Retrieve multiple series from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of objects.
	 */
	public function get_series( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'      => false,
			'number'  => 20,
			'offset'  => 0,
			'name'    => false,
			'slug'    => false,
			'author'  => false, // @todo Make this work - join needed.
			'orderby' => 'ID',
			'order'   => 'DESC',
			'fields'  => 'all'
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific series.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Series with a specific name.
		if ( ! empty( $args['name'] ) ) {
			$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", wp_strip_all_tags( $args['name'] ) );
		}

		// Series with a specific slug.
		if ( ! empty( $args['slug'] ) ) {
			$where .= $wpdb->prepare( " AND `slug` = %s ", wp_strip_all_tags( $args['slug'] ) );
		}

		// @todo author

		$orderby = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'ID' : wp_strip_all_tags( $args['orderby'] );
		$order   = ( 'ASC' == strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		$select_this = '*';
		if ( 'names' == $args['fields'] ) {
			$select_this = 'series.name';
		} elseif ( 'ids' == $args['fields'] ) {
			$select_this = 'series.ID';
		}

		$cache_key = md5( 'bdb_series_' . serialize( $args ) );

		$series = wp_cache_get( $cache_key, 'series' );

		if ( $series === false ) {
			$query = $wpdb->prepare( "SELECT $select_this FROM  $this->table_name AS series $join $where GROUP BY $this->primary_key ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			if ( 'names' == $args['fields'] || 'ids' == $args['fields'] ) {
				$series = $wpdb->get_col( $query );
			} else {
				$series = $wpdb->get_results( $query );
			}
			wp_cache_set( $cache_key, $series, 'series', 3600 );
		}

		return $series;

	}

	/**
	 * Count the total number of series in the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'     => false,
			'name'   => false,
			'author' => false, // @todo Make this work - join needed.
		);

		$args = wp_parse_args( $args, $defaults );

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific series.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Series with a specific name.
		if ( ! empty( $args['author'] ) ) {
			$where .= $wpdb->prepare( " AND `author` LIKE '%%%%" . '%s' . "%%%%' ", wp_strip_all_tags( $args['author'] ) );
		}

		// @todo author

		$cache_key = md5( 'bdb_series_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'series' );

		if ( $count === false ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'series', 3600 );
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
		name varchar(200) NOT NULL,
		slug varchar(200) NOT NULL,
		description longtext NOT NULL,
		PRIMARY KEY  (ID),
		INDEX name (name)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

}