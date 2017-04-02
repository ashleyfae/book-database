<?php

/**
 * Reading List DB Class
 *
 * This class is for interacting with the reading list table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Reading_List extends BDB_DB {

	/**
	 * BDB_DB_Reading_List constructor.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'bdb_reading_list';
		$this->primary_key = 'ID';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'ID'            => '%d',
			'book_id'       => '%d',
			'review_id'     => '%d',
			'user_id'       => '%d',
			'date_started'  => '%s',
			'date_finished' => '%s',
			'complete'      => '%d',
			'rating'        => '%s'
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
			'book_id'       => 0,
			'review_id'     => 0,
			'user_id'       => 0,
			'date_started'  => null,
			'date_finished' => null,
			'complete'      => 0,
			'rating'        => null
		);
	}

	/**
	 * Add a Reading Entry
	 *
	 * @param array $data Entry data.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return int Entry ID.
	 */
	public function add( $data = array() ) {

		$defaults = array();

		$args = wp_parse_args( $data, $defaults );

		$entry = ( array_key_exists( 'ID', $args ) ) ? $this->get_entry_by( 'ID', $args['ID'] ) : false;

		if ( $entry ) {

			// Updating an existing review.
			$this->update( $entry->ID, $args );

			return $entry->ID;

		} else {

			// Adding a new review.
			return $this->insert( $args, 'reading_list' );

		}

	}

	/**
	 * Delete an Entry
	 *
	 * @param bool $id ID of the entry to delete.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return bool|int False on failure.
	 */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$entry = $this->get_entry_by( 'ID', $id );

		if ( $entry->ID > 0 ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'ID' => $entry->ID ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Retrieves a single entry, given an ID.
	 *
	 * @param int $entry_id Entry ID to fetch.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return object|false Upon success, an object of the entry. Upon failure, false.
	 */
	public function get_entry( $entry_id ) {

		return $this->get_entry_by( 'ID', $entry_id );

	}

	/**
	 * Retrieves a single entry from the database.
	 *
	 * @param string $field The column to search.
	 * @param int    $value The value to check against the column.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return object|false Upon success, an object of the entry. Upon failure, false.
	 */
	public function get_entry_by( $field = 'ID', $value = 0 ) {

		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( $field == 'ID' || $field == 'book_id' || $field == 'review_id' || $field == 'user_id' ) {
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

			case 'book_id' :
				$db_field = 'book_id';
				break;

			case 'review_id' :
				$db_field = 'review_id';
				break;

			case 'user_id' :
				$db_field = 'user_id';
				break;

			default :
				return false;

		}

		if ( ! $entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			return false;

		}

		return wp_unslash( $entry );

	}

	/**
	 * Retrieve entries from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of objects.
	 */
	public function get_entries( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'            => false,
			'number'        => 20,
			'offset'        => 0,
			'book_id'       => false,
			'review_id'     => false,
			'user_id'       => false,
			'orderby'       => 'ID',
			'order'         => 'DESC',
			'date_started'  => false,
			'date_finished' => false,
			'rating'        => false
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific entries.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Entries for a given book.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
		}

		// Entries for a specific review.
		if ( ! empty( $args['review_id'] ) ) {
			if ( is_array( $args['review_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['review_id'] ) );
			} else {
				$ids = intval( $args['review_id'] );
			}
			$where .= " AND `review_id` IN( {$ids} ) ";
		}

		// Entries for specific user accounts.
		if ( ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map( 'intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}
			$where .= " AND `user_id` IN( {$user_ids} ) ";
		}

		// By start date.
		if ( ! empty( $args['date_started'] ) ) {

			if ( is_array( $args['date_started'] ) ) {

				if ( ! empty( $args['date_started']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $args['date_started']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_started` >= %s", $start );
				}

				if ( ! empty( $args['date_started']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $args['date_started']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_started` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $args['date_started'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $args['date_started'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $args['date_started'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_started ) AND %d = MONTH ( date_started ) AND %d = DAY ( date_started )", $year, $month, $day );

			}

		}

		// By finish date.
		if ( ! empty( $args['date_finished'] ) ) {

			if ( is_array( $args['date_finished'] ) ) {

				if ( ! empty( $args['date_finished']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $args['date_finished']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_finished` >= %s", $start );
				}

				if ( ! empty( $args['date_finished']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $args['date_finished']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_finished` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $args['date_finished'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $args['date_finished'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $args['date_finished'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_finished ) AND %d = MONTH ( date_finished ) AND %d = DAY ( date_finished )", $year, $month, $day );

			}

		}

		// By specific rating.
		if ( ! empty( $args['rating'] ) ) {
			$where .= $wpdb->prepare( " AND `rating` LIKE '" . '%s' . "' ", wp_strip_all_tags( $args['rating'] ) );
		}

		$orderby = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'ID' : wp_strip_all_tags( $args['orderby'] );
		$order   = ( 'ASC' == strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		$cache_key = md5( 'bdb_reading_list_' . serialize( $args ) );

		$entries = wp_cache_get( $cache_key, 'reading_list' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( $entries === false ) {
			$query   = $wpdb->prepare( "SELECT * FROM  $this->table_name AS reading_list $join $where GROUP BY $this->primary_key ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$entries = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $entries, 'reading_list', 3600 );
		}

		return wp_unslash( $entries );

	}

	/**
	 * Count the total number of entries in the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return int
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'            => false,
			'number'        => 20,
			'offset'        => 0,
			'book_id'       => false,
			'review_id'     => false,
			'user_id'       => false,
			'date_started'  => false,
			'date_finished' => false,
			'rating'        => false
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific entries.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Entries for a given book.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
		}

		// Entries for a specific review.
		if ( ! empty( $args['review_id'] ) ) {
			if ( is_array( $args['review_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['review_id'] ) );
			} else {
				$ids = intval( $args['review_id'] );
			}
			$where .= " AND `review_id` IN( {$ids} ) ";
		}

		// Entries for specific user accounts.
		if ( ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map( 'intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}
			$where .= " AND `user_id` IN( {$user_ids} ) ";
		}

		// By start date.
		if ( ! empty( $args['date_started'] ) ) {

			if ( is_array( $args['date_started'] ) ) {

				if ( ! empty( $args['date_started']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $args['date_started']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_started` >= %s", $start );
				}

				if ( ! empty( $args['date_started']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $args['date_started']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_started` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $args['date_started'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $args['date_started'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $args['date_started'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_started ) AND %d = MONTH ( date_started ) AND %d = DAY ( date_started )", $year, $month, $day );

			}

		}

		// By finish date.
		if ( ! empty( $args['date_finished'] ) ) {

			if ( is_array( $args['date_finished'] ) ) {

				if ( ! empty( $args['date_finished']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $args['date_finished']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_finished` >= %s", $start );
				}

				if ( ! empty( $args['date_finished']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $args['date_finished']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_finished` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $args['date_finished'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $args['date_finished'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $args['date_finished'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_finished ) AND %d = MONTH ( date_finished ) AND %d = DAY ( date_finished )", $year, $month, $day );

			}

		}

		// Specific rating.
		if ( ! empty( $args['rating'] ) ) {
			$where .= $wpdb->prepare( " AND `rating` LIKE '" . '%s' . "' ", wp_strip_all_tags( $args['rating'] ) );
		}

		$cache_key = md5( 'bdb_reading_list_' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'reading_list' );

		if ( $count === false ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'reading_list', 3600 );
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
		book_id bigint(20) NOT NULL,
		review_id bigint(20) NOT NULL,
		user_id bigint(20) NOT NULL,
		date_started datetime,
		date_finished datetime,
		complete bigint(3) NOT NULL,
		rating varchar(32),
		PRIMARY KEY (ID),
		KEY rating_book_id (rating, book_id),
		KEY rating_review_id (rating, review_id),
		INDEX book_id (book_id),
		INDEX review_id (review_id),
		INDEX user_id (user_id),
		INDEX date_started (date_started),
		INDEX date_finished (date_finished),
		INDEX complete (complete),
		INDEX rating (rating),
		INDEX date_finished_complete (date_finished, complete),
		INDEX date_finished_rating (date_finished, rating),
		INDEX date_finished_book_id (date_finished, book_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

}