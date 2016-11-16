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

			global $wpdb;
			$review_table       = book_database()->reviews->table_name;
			$book_table         = book_database()->books->table_name;
			$relationship_table = book_database()->book_term_relationships->table_name;
			$term_table         = book_database()->book_terms->table_name;

			$query = $wpdb->prepare( "SELECT DISTINCT review.ID, review.rating, review.date_added,
										book.ID as book_id, book.title as book_title,
										author.name as author_name
									FROM {$review_table} as review
									INNER JOIN {$book_table} as book ON review.book_id = book.ID
									LEFT JOIN {$relationship_table} as r ON book.ID = r.book_id
									INNER JOIN {$term_table} as author ON (r.term_id = author.term_id AND author.type = 'author')
									WHERE `date_added` >= %s
									AND `date_added` <= %s
									ORDER BY review.date_added DESC",
				date( 'Y-m-d 00:00:00', self::$start ),
				date( 'Y-m-d 00:00:00', self::$end )
			);

			self::$reviews = $wpdb->get_results( $query );

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
		$book_ids_string = implode( ',', array_map( 'absint', $book_ids ) );

		$query       = "SELECT SUM(pages) FROM {$book_table} WHERE `ID` IN ({$book_ids_string})";
		$pages       = $wpdb->get_var( $query );
		$total_pages = number_format( absint( $pages ) );

		return $total_pages;

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

	/**
	 * Get Book List
	 *
	 * Returns an array of the list of books reviewed in this time.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_book_list() {

		$reviews     = self::$instance->query_reviews();
		$list        = array();
		$date_format = get_option( 'date_format' );

		if ( is_array( $reviews ) ) {
			// Maybe slice array.
			if ( count( $reviews ) > 20 ) {
				$reviews = array_slice( $reviews, 0, 20 );
			}

			foreach ( $reviews as $review ) {
				$rating = new BDB_Rating( $review->rating );
				$list[] = array(
					'book'             => sprintf( _x( '%s by %s', 'book title by author', 'book-database' ), $review->book_title, $review->author_name ),
					'rating'           => $rating->format( 'html_stars' ),
					'rating_class'     => sanitize_html_class( $rating->format( 'html_class' ) ),
					'edit_review_link' => bdb_get_admin_page_edit_review( absint( $review->ID ) ),
					'edit_book_link'   => bdb_get_admin_page_edit_book( absint( $review->book_id ) ),
					'date'             => mysql2date( $date_format, $review->date_added )
				);
			}
		}

		return $list;

	}

	/**
	 * Get Rating Breakdown
	 *
	 * Returns a table containing the rating breakdown for the time period. It's an array of all available
	 * ratings and the number of reviews for that rating.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_rating_breakdown() {

		global $wpdb;
		$reviews_table = book_database()->reviews->table_name;

		$query   = $wpdb->prepare( "SELECT rating, COUNT(rating) AS count FROM {$reviews_table} WHERE `date_added` >= %s AND `date_added` <= %s GROUP BY rating ORDER BY rating + 0 DESC", date( 'Y-m-d 00:00:00', self::$start ), date( 'Y-m-d 00:00:00', self::$end ) );
		$results = $wpdb->get_results( $query );
		//file_put_contents( BDB_DIR . 'log.txt', $query . "\n\n", FILE_APPEND );

		$available_ratings = bdb_get_available_ratings();
		$final_array       = array();

		foreach ( $available_ratings as $key => $name ) {
			$final_array[ $key ] = 0;
		}

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				if ( array_key_exists( $result->rating, $final_array ) ) {
					$final_array[ $result->rating ] = $result->count;
				}
			}
		}

		return $final_array;

	}

	/**
	 * Terms Breakdown
	 *
	 * Returns an array of all terms used in the book reviews in this period with
	 * the following information:
	 *      + Number of reviews for this term.
	 *      + Average rating for this term.
	 *
	 * @param bool $term_type
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_terms_breakdown( $term_type = false ) {

		$types = bdb_get_taxonomies();

		global $wpdb;
		$review_table       = book_database()->reviews->table_name;
		$relationship_table = book_database()->book_term_relationships->table_name;
		$term_table         = book_database()->book_terms->table_name;

		$where = '';

		if ( $term_type && array_key_exists( $term_type, $types ) ) {
			$where .= $wpdb->prepare( " AND term.type = %s", wp_strip_all_tags( sanitize_text_field( $term_type ) ) );
		}

		$query = $wpdb->prepare( "SELECT COUNT(rating) as number_reviews, ROUND(AVG(IF(rating = 'dnf', 0, rating)), 2) as avg_rating, term.name, term.type
									FROM {$review_table} reviews
									INNER JOIN {$relationship_table} r on r.book_id = reviews.book_id
									INNER JOIN {$term_table} term on term.term_id = r.term_id
									WHERE `date_added` >= %s
									AND `date_added` <= %s
									{$where}
									GROUP BY term.type, term.name
									ORDER BY term.name ASC",
			date( 'Y-m-d 00:00:00', self::$start ),
			date( 'Y-m-d 00:00:00', self::$end )
		);

		//file_put_contents( BDB_DIR . 'log.txt', $query . "\n\n", FILE_APPEND );

		$results = $wpdb->get_results( $query );

		return $results;

	}

}