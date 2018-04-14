<?php

/**
 * Reviews DB Class
 *
 * This class is for interacting with the reviews database table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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
	 * @since  1.0
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
	 * @since  1.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'ID'             => '%d',
			'book_id'        => '%d',
			'post_id'        => '%d',
			'url'            => '%s',
			'user_id'        => '%d',
			'review'         => '%s',
			'date_written'   => '%s',
			'date_published' => '%s'
		);
	}

	/**
	 * Get default column values.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'book_id'        => 0,
			'post_id'        => 0,
			'url'            => '',
			'user_id'        => 0,
			'review'         => '',
			'date_written'   => gmdate( 'Y-m-d H:i:s' ),
			'date_published' => null,
		);
	}

	/**
	 * Add a Review
	 *
	 * @param array $data Review data.
	 *
	 * @access public
	 * @since  1.0
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
	 * @since  1.0
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
	 * Check if a review exists.
	 *
	 * @param string $value Value of the column.
	 * @param string $field Which field to check.
	 *
	 * @access public
	 * @since  1.0
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
	 * @since  1.0
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
	 * @since  1.0
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

		return wp_unslash( $review );

	}

	/**
	 * Retrieve reviews from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of objects.
	 */
	public function get_reviews( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'                 => false,
			'number'             => 20,
			'offset'             => 0,
			'book_id'            => false,
			'post_id'            => false,
			'user_id'            => 0,
			'rating'             => false,
			'orderby'            => 'ID',
			'order'              => 'DESC',
			'date_written'       => false,
			'include_book_title' => false,
			'include_author'     => false,
			'book_title'         => false, // Only works if `include_book_title` is `true`.
			'author_name'        => false, // Only works if `include_author` is `true`.
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$select = '';
		$join   = '';
		$where  = ' WHERE 1=1 ';

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
			$where .= " AND review.book_id IN( {$ids} ) ";
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

		// Include book title.
		if ( $args['include_book_title'] ) {
			$book_table = book_database()->books->table_name;
			$select .= ", book.title as book_title";
			$join .= " LEFT JOIN {$book_table} as book on book.ID = review.book_id";

			if ( $args['book_title'] ) {
				$where .= $wpdb->prepare( " AND `title` LIKE '%%%%" . '%s' . "%%%%' ", sanitize_text_field( wp_strip_all_tags( $args['book_title'] ) ) );
			}
		}

		// Include book author.
		if ( $args['include_author'] ) {
			$terms_table = book_database()->book_terms->table_name;
			$r_table     = book_database()->book_term_relationships->table_name;
			$select .= ", GROUP_CONCAT(author.name SEPARATOR ', ') as author_name";
			$join .= " LEFT JOIN {$r_table} as r on r.book_id = review.book_id";
			$join .= " INNER JOIN {$terms_table} as author on (author.term_id = r.term_id AND author.type = 'author')";

			if ( $args['author_name'] ) {
				$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", sanitize_text_field( wp_strip_all_tags( $args['author_name'] ) ) );
			}
		}

		// Always join on reading log to get rating.
		$reading_table = book_database()->reading_log->table_name;
		$select .= ", log.rating";
		$join .= " LEFT JOIN {$reading_table} as log on log.review_id = review.ID";

		// Reviews with a specific rating.
		if ( ! empty( $args['rating'] ) ) {
			$where .= $wpdb->prepare( " AND log.rating LIKE '" . '%s' . "' ", sanitize_text_field( $args['rating'] ) );
		}

		// Reviews created for a specific date or in a date range.
		if ( ! empty( $args['date_written'] ) ) {

			if ( is_array( $args['date_written'] ) ) {

				if ( ! empty( $args['date_written']['start'] ) ) {
					$start = get_gmt_from_date( sanitize_text_field( $args['date_written']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_written` >= %s", $start );
				}

				if ( ! empty( $args['date_written']['end'] ) ) {
					$end = get_gmt_from_date( sanitize_text_field( $args['date_written']['end'] ), 'Y-m-d 23:59:59' );
					$wpdb->prepare( " AND `date_written` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( sanitize_text_field( $args['date_written'] ), 'Y' );
				$month = get_gmt_from_date( sanitize_text_field( $args['date_written'] ), 'm' );
				$day   = get_gmt_from_date( sanitize_text_field( $args['date_written'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_written ) AND %d = MONTH ( date_written ) AND %d = DAY ( date_written )", $year, $month, $day );

			}

		}

		if ( 'date' == $args['orderby'] ) {
			$args['orderby'] = 'date_written';
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'ID' : $args['orderby'];

		$cache_key = md5( 'bdb_reviews_' . serialize( $args ) );

		$reviews = wp_cache_get( $cache_key, 'reviews' );

		// This is no longer relevant since ditching the 'rating' column.
		/*if ( 'rating' == $args['orderby'] ) {
			$args['orderby'] = $args['orderby'] . " * 1";
		}*/

		$orderby = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'ID' : wp_strip_all_tags( $args['orderby'] );
		$order   = ( 'ASC' == strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		if ( $reviews === false ) {
			$query   = $wpdb->prepare( "SELECT review.*$select FROM  $this->table_name AS review $join $where GROUP BY $this->primary_key ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$reviews = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $reviews, 'reviews', 3600 );
		}

		return wp_unslash( $reviews );

	}

	/**
	 * Count the total number of reviews in the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'           => false,
			'book_id'      => false,
			'post_id'      => false,
			'user_id'      => 0,
			'rating'       => false,
			'date_written' => false
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
			$reading_table = book_database()->reading_log->table_name;
			$join .= " LEFT JOIN {$reading_table} as log on log.review_id = review.ID";
			$where .= $wpdb->prepare( " AND log.rating LIKE '" . '%s' . "' ", sanitize_text_field( $args['rating'] ) );
		}

		// Reviews created for a specific date or in a date range.
		if ( ! empty( $args['date_written'] ) ) {

			if ( is_array( $args['date_written'] ) ) {

				if ( ! empty( $args['date_written']['start'] ) ) {
					$start = get_gmt_from_date( sanitize_text_field( $args['date_written']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_written` >= %s", $start );
				}

				if ( ! empty( $args['date_written']['end'] ) ) {
					$end = get_gmt_from_date( sanitize_text_field( $args['date_written']['end'] ), 'Y-m-d 23:59:59' );
					$wpdb->prepare( " AND `date_written` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( sanitize_text_field( $args['date_written'] ), 'Y' );
				$month = get_gmt_from_date( sanitize_text_field( $args['date_written'] ), 'm' );
				$day   = get_gmt_from_date( sanitize_text_field( $args['date_written'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_written ) AND %d = MONTH ( date_written ) AND %d = DAY ( date_written )", $year, $month, $day );

			}

		}

		$cache_key = md5( 'bdb_reviews_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'reviews' );

		if ( $count === false ) {
			$query = "SELECT COUNT(review.ID) FROM $this->table_name AS review {$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'reviews', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table.
	 *
	 * @access public
	 * @since  1.0
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
		review longtext NOT NULL,
		date_written datetime NOT NULL,
		date_published datetime,
		PRIMARY KEY  (ID),
		INDEX date_written_ID (date_written, ID),
		INDEX date_written_book_id (date_written, book_id),
		INDEX book_id (book_id),
		INDEX post_id (post_id),
		INDEX user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

}