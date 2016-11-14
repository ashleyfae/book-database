<?php

/**
 * Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_Analytics
 *
 * @since 1.0.0
 */
class BDB_Analytics {

	/**
	 * Single class instance.
	 *
	 * @var BDB_Analytics
	 * @access public
	 * @since  1.0.0
	 */
	public static $instance;

	/**
	 * Start - Timestamp
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public static $start = null;

	/**
	 * End - Timestamp
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public static $end = null;

	/**
	 * Start - String
	 *
	 * Readable string of time. Default is "-30 days".
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public static $startstr = null;

	/**
	 * End - String
	 *
	 * Readable string of time. Default is "now".
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public static $endstr = null;

	/**
	 * Array of reviews that were created during this period.
	 *
	 * @var array
	 * @access public
	 * @since  1.0.0
	 */
	public static $reviews;

	/**
	 * Array of book IDs read during this period.
	 *
	 * @var array
	 * @access public
	 * @since  1.0.0
	 */
	public static $book_ids;

	/**
	 * Get Instance
	 *
	 * @access public
	 * @since  1.0.0
	 * @return BDB_Analytics
	 */
	public static function instance() {

		if ( ! self::$instance ) {
			self::$instance = new BDB_Analytics();
		}

		self::$instance->set_dates();

		return self::$instance;

	}

	/**
	 * Set Dates
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function set_dates( $start = '-30 days', $end = 'now' ) {

		self::$startstr = $start;
		self::$endstr   = $end;
		self::$start    = strtotime( self::$startstr );
		self::$end      = strtotime( self::$endstr );

	}

	/**
	 * Query Reviews
	 *
	 * Returns an array of all review objects created during this period.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function query_reviews() {

		if ( ! isset( self::$reviews ) ) {

			self::$reviews = book_database()->reviews->get_reviews( array(
				'number'     => - 1,
				'date_added' => array(
					'start' => self::$startstr,
					'end'   => self::$endstr
				)
			) );

		}

		return self::$reviews;

	}

	/**
	 * Get Book IDs
	 *
	 * Array of all book IDs read during this period.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_book_ids() {

		if ( ! isset( self::$book_ids ) ) {

			$book_ids = array();
			$reviews  = self::$instance->query_reviews();

			if ( is_array( $reviews ) ) {
				foreach ( $reviews as $review ) {
					$book_ids[] = $review->book_id;
				}
			}

			self::$book_ids = $book_ids;

		}

		return self::$book_ids;

	}

	/**
	 * Get Number of Reviews
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function get_number_reviews() {

		$reviews = self::$instance->query_reviews();

		return is_array( $reviews ) ? count( $reviews ) : 0;

	}

	/**
	 * Get Pages Read
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function get_pages_read() {

		global $wpdb;
		$book_table      = book_database()->books->table_name;
		$book_ids        = self::$instance->get_book_ids();
		$book_ids_string = implode( ',', array_map( 'intval', $book_ids ) );
		$total_pages     = 0;

		$query = $wpdb->prepare( "SELECT pages FROM {$book_table} WHERE `ID` IN (%s)", $book_ids_string );
		$pages = $wpdb->get_col( $query );

		foreach ( $pages as $page ) {
			if ( $page ) {
				$total_pages = $total_pages + absint( $page );
			}
		}

		return absint( $total_pages );

	}

	/**
	 * Get Average Rating
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|float
	 */
	public function get_average_rating() {

		$total_count = $number_ratings = 0;
		$reviews     = self::$instance->query_reviews();

		if ( is_array( $reviews ) ) {
			foreach ( $reviews as $review ) {
				$rating = $review->rating;

				if ( ! is_numeric( $rating ) ) {
					$rating = 0; // DNF is counted as 0.
				}

				$total_count = $total_count + $rating;
				$number_ratings ++;
			}
		}

		$average = round( $total_count / $number_ratings, 1 );

		return $average;

	}

}