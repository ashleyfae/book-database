<?php

/**
 * UBB DB Base Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class BDB_DB {

	/**
	 * The name of our database table.
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public $table_name;

	/**
	 * The version of our database table.
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public $version;

	/**
	 * The name of the primary column.
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public $primary_key;

	/**
	 * BDB_DB constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Whitelist of columns.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrive a row by the primary key.
	 *
	 * @param int|mixed $row_id
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object
	 */
	public function get( $row_id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a row by a specific column/value.
	 *
	 * @param string     $column Name of the column.
	 * @param string|int $row_id Value of the column.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrive a specific column's value by the primary key.
	 *
	 * @param string    $column Name of the column to fetch.
	 * @param int|mixed $row_id Primary key value.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );

		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value.
	 *
	 * @param string $column       Name of the column to fetch.
	 * @param string $column_where Name of the column to match the value against.
	 * @param string $column_value Value of the previous column
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );

		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}

	/**
	 * Insert a new row.
	 *
	 * @param array  $data Row data.
	 * @param string $type Type of table.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function insert( $data, $type = '' ) {

		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'book-database/db/pre-insert-' . $type, $data );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

		do_action( 'book-database/db/post-insert-' . $type, $wpdb->insert_id, $data );

		return $wpdb->insert_id;

	}

	/**
	 * Update a row
	 *
	 * @param int    $row_id ID of the row to update.
	 * @param array  $data   New data to insert in the row.
	 * @param string $where  Column to match the ID against.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Delete a row identified by the primary key.
	 *
	 * @param int $row_id ID of the row to delete.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Check if the given table exists.
	 *
	 * @param string $table Name of the table to check.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function table_exists( $table ) {

		global $wpdb;
		$table = sanitize_text_field( $table );

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;

	}

	/**
	 * Check if the table was ever installed.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

}