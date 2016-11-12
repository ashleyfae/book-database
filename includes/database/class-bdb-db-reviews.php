<?php

/**
 * Reviews DB Class
 *
 * This class is for interacting with the reviews database table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Reviews extends BDB_DB {

	/**
	 * BDB_DB_Reviews constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'bdb_reviews';
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
			'ID'         => '%d',
			'book_id'    => '%d',
			'post_id'    => '%d',
			'url'        => '%s',
			'user_id'    => '%d',
			'rating'     => '%s',
			'date_added' => '%s'
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
			'book_id'    => 0,
			'post_id'    => 0,
			'url'        => '',
			'user_id'    => 0,
			'rating'     => '',
			'date_added' => date( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * Add a Review
	 *
	 * @param array $data Review data.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int Review ID.
	 */
	public function add( $data = array() ) {

		$defaults = array();

		$args = wp_parse_args( $data, $defaults );

		$review = ( array_key_exists( 'ID', $args ) ) ? $this->get_review_by( 'ID', $args['ID'] ) : false;

		if ( $review ) {

			// Updating an existing review.
			$this->update( $review->ID, $args );

			return $review->ID;

		} else {

			// Adding a new review.
			return $this->insert( $args, 'review' );

		}

	}

	/**
	 * Delete a review.
	 *
	 * @param bool $id ID of the review to delete.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|int False on failure.
	 */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$review = $this->get_review_by( 'ID', $id );

		if ( $review->ID > 0 ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'ID' => $review->ID ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Delete Multiple Reviews by IDs
	 *
	 * @param array $ids Array of review IDs.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false Number of rows deleted or false if none.
	 */
	public function delete_by_ids( $ids ) {

		global $wpdb;

		if ( is_array( $ids ) ) {
			$ids = implode( ',', array_map( 'intval', $ids ) );
		} else {
			$ids = intval( $ids );
		}

		$results = $wpdb->query( "DELETE FROM  $this->table_name WHERE `ID` IN( {$ids} )" );

		return $results;

	}

	/**
	 * Check if a review exists.
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
	 * Retrieves a single review, given an ID.
	 *
	 * @param int $review_id Review ID to fetch.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object|false Upon success, an object of the review. Upon failure, false.
	 */
	public function get_review( $review_id ) {

		return $this->get_review_by( 'ID', $review_id );

	}

	/**
	 * Retrieves a single review from the database.
	 *
	 * @param string $field The column to search.
	 * @param int    $value The value to check against the column.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object|false Upon success, an object of the review. Upon failure, false.
	 */
	public function get_review_by( $field = 'ID', $value = 0 ) {

		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( $field == 'ID' || $field == 'book_id' || $field == 'post_id' || $field == 'user_id' ) {
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

			case 'post_id' :
				$db_field = 'post_id';
				break;

			case 'user_id' :
				$db_field = 'user_id';
				break;

			default :
				return false;

		}

		if ( ! $review = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			return false;

		}

		return stripslashes_deep( $review );

	}

	/**
	 * Retrieve reviews from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of objects.
	 */
	public function get_reviews( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'         => false,
			'number'     => 20,
			'offset'     => 0,
			'book_id'    => false,
			'post_id'    => false,
			'user_id'    => 0,
			'rating'     => false,
			'orderby'    => 'ID',
			'order'      => 'DESC',
			'date_added' => false
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific reviews.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Reviews for specific user accounts.
		if ( ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map( 'intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}
			$where .= " AND `user_id` IN( {$user_ids} ) ";
		}

		// Specific reviews for a given book.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
		}

		// Specific reviews for a given post.
		if ( ! empty( $args['post_id'] ) ) {
			if ( is_array( $args['post_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['post_id'] ) );
			} else {
				$ids = intval( $args['post_id'] );
			}
			$where .= " AND `post_id` IN( {$ids} ) ";
		}

		// Reviews with a specific rating.
		if ( ! empty( $args['rating'] ) ) {
			$where .= $wpdb->prepare( " AND `rating` LIKE '%%%%" . '%s' . "%%%%' ", $args['rating'] ); // @todo check for word rating settings
		}

		// Reviews created for a specific date or in a date range.
		if ( ! empty( $args['date_added'] ) ) {

			if ( is_array( $args['date_added'] ) ) {

				if ( ! empty( $args['date_added']['start'] ) ) {
					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date_added']['start'] ) );
					$where .= " AND `date_added` >= '{$start}'";
				}

				if ( ! empty( $args['date_added']['end'] ) ) {
					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date_added']['end'] ) );
					$where .= " AND `date_added` <= '{$end}'";
				}

			} else {

				$year  = date( 'Y', strtotime( $args['date_added'] ) );
				$month = date( 'm', strtotime( $args['date_added'] ) );
				$day   = date( 'd', strtotime( $args['date_added'] ) );
				$where .= " AND $year = YEAR ( date_added ) AND $month = MONTH ( date_added ) AND $day = DAY ( date_added )";

			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'ID' : $args['orderby'];

		$cache_key = md5( 'bdb_reviews_' . serialize( $args ) );

		$reviews = wp_cache_get( $cache_key, 'reviews' );

		if ( 'rating' == $args['orderby'] ) {
			$args['orderby'] = $args['orderby'] . " * 1";
		}

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( $reviews === false ) {
			$query   = $wpdb->prepare( "SELECT * FROM  $this->table_name $join $where GROUP BY $this->primary_key ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$reviews = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $reviews, 'reviews', 3600 );
		}

		return stripslashes_deep( $reviews );

	}

	/**
	 * Count the total number of reviews in the database.
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
			'ID'         => false,
			'book_id'    => false,
			'post_id'    => false,
			'user_id'    => 0,
			'rating'     => false,
			'date_added' => false
		);

		$args = wp_parse_args( $args, $defaults );

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific reviews.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Reviews for specific user accounts.
		if ( ! empty( $args['user_id'] ) ) {
			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map( 'intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}
			$where .= " AND `user_id` IN( {$user_ids} ) ";
		}

		// Specific reviews for a given book.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
		}

		// Specific reviews for a given post.
		if ( ! empty( $args['post_id'] ) ) {
			if ( is_array( $args['post_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['post_id'] ) );
			} else {
				$ids = intval( $args['post_id'] );
			}
			$where .= " AND `post_id` IN( {$ids} ) ";
		}

		// Reviews with a specific rating.
		if ( ! empty( $args['rating'] ) ) {
			$where .= $wpdb->prepare( " AND `rating` LIKE '%%%%" . '%s' . "%%%%' ", $args['rating'] ); // @todo check for word rating settings
		}

		// Reviews created for a specific date or in a date range.
		if ( ! empty( $args['date_added'] ) ) {

			if ( is_array( $args['date_added'] ) ) {

				if ( ! empty( $args['date_added']['start'] ) ) {
					$start = date( 'Y-m-d 00:00:00', strtotime( $args['date_added']['start'] ) );
					$where .= " AND `date_added` >= '{$start}'";
				}

				if ( ! empty( $args['date_added']['end'] ) ) {
					$end = date( 'Y-m-d 23:59:59', strtotime( $args['date_added']['end'] ) );
					$where .= " AND `date_added` <= '{$end}'";
				}

			} else {

				$year  = date( 'Y', strtotime( $args['date_added'] ) );
				$month = date( 'm', strtotime( $args['date_added'] ) );
				$day   = date( 'd', strtotime( $args['date_added'] ) );
				$where .= " AND $year = YEAR ( date_added ) AND $month = MONTH ( date_added ) AND $day = DAY ( date_added )";

			}

		}

		$cache_key = md5( 'bdb_reviews_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'reviews' );

		if ( $count === false ) {
			$query = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'reviews', 3600 );
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
		post_id bigint(20) NOT NULL,
		url mediumtext NOT NULL,
		user_id bigint(20) NOT NULL,
		rating mediumtext NOT NULL,
		date_added datetime NOT NULL,
		PRIMARY KEY  (ID),
		INDEX book_id (book_id),
		INDEX post_id (post_id),
		INDEX user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

}