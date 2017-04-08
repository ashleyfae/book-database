<?php

/**
 * Review Meta DB Class
 *
 * This class is for interacting with the review meta database table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Review_Meta extends BDB_DB {

	/**
	 * BDB_DB_Review_Meta constructor.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name = $wpdb->prefix . 'bdb_reviewmeta';

		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		add_action( 'plugins_loaded', array( $this, 'register_table' ), 11 );
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'meta_id'    => '%d',
			'review_id'  => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s'
		);
	}

	/**
	 * Register the table with $wpdb so the metadata API can find it.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function register_table() {
		global $wpdb;

		$wpdb->reviewmeta = $this->table_name;
	}

	/**
	 * Retrieve meta field for a review.
	 *
	 * For internal use only. Use `BDB_Review->get_meta()` for public usage.
	 *
	 * @param   int    $review_id Review ID.
	 * @param   string $meta_key  The meta key to retrieve.
	 * @param   bool   $single    Whether to return a single value.
	 *
	 * @access  public
	 * @since   1.0
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $review_id = 0, $meta_key = '', $single = false ) {
		$review_id = $this->sanitize_review_id( $review_id );

		if ( false === $review_id ) {
			return false;
		}

		return get_metadata( 'review', $review_id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a review.
	 *
	 * For internal use only. Use BDB_Review->add_meta() for public usage.
	 *
	 * @param int    $review_id  Review ID.
	 * @param string $meta_key   Meta name.
	 * @param  mixed $meta_value Meta value.
	 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool False for failure, true for success.
	 */
	public function add_meta( $review_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$review_id = $this->sanitize_review_id( $review_id );

		if ( false === $review_id ) {
			return false;
		}

		return add_metadata( 'review', $review_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update review meta field based on review ID.
	 *
	 * For internal use only. Use `BDB_Review->update_meta()` for public usage.
	 *
	 * If the meta field for the review does not exist, it will be added.
	 *
	 * @param int    $review_id  Review ID.
	 * @param string $meta_key   Meta name.
	 * @param mixed  $meta_value Meta value.
	 * @param string $prev_value Optional. Previous value to check before removing.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool False for failure, true for success.
	 */
	public function update_meta( $review_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$review_id = $this->sanitize_review_id( $review_id );

		if ( false === $review_id ) {
			return false;
		}

		return update_metadata( 'review', $review_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a review.
	 *
	 * For internal use only. Use `BDB_Review->delete_meta()` for public usage.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param int    $review_id  Review ID.
	 * @param string $meta_key   Meta name.
	 * @param mixed  $meta_value Meta value.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool False for failure, true for success.
	 */
	public function delete_meta( $review_id = 0, $meta_key = '', $meta_value ) {
		return delete_metadata( 'review', $review_id, $meta_key, $meta_value );
	}

	/**
	 * Create the table
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			review_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY review_id (review_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

	/**
	 * Sanitize review ID
	 *
	 * Given a review ID, make sure it's a positive number before inserting
	 * or adding it.
	 *
	 * @param int|string $review_id A passed review ID.
	 *
	 * @access private
	 * @since  1.0
	 * @return int|bool The normalized review ID or false if it's found to not be valid.
	 */
	private function sanitize_review_id( $review_id ) {

		if ( ! is_numeric( $review_id ) ) {
			return false;
		}

		$review_id = (int) $review_id;

		// We were given a negative number.
		if ( absint( $review_id ) !== $review_id ) {
			return false;
		}

		if ( empty( $review_id ) ) {
			return false;
		}

		return absint( $review_id );

	}

}