<?php

/**
 * Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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
	 * Start - GMT Date
	 *
	 * Start date converted to GMT.
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public static $start = null;

	/**
	 * End - GMT Date
	 *
	 * End date converted to GMT.
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
		self::$start    = get_gmt_from_date( self::$startstr, 'Y-m-d 00:00:00' );
		self::$end      = get_gmt_from_date( self::$endstr, 'Y-m-d 23:59:59' );

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
			$reading_table      = book_database()->reading_list->table_name;

			$query = $wpdb->prepare( "SELECT DISTINCT review.ID, review.date_written,
										log.rating as rating,
										book.ID as book_id, book.title as book_title,
										GROUP_CONCAT(author.name SEPARATOR ', ') as author_name
									FROM {$review_table} as review
									LEFT JOIN {$reading_table} as log on review.ID = log.review_id
									INNER JOIN {$book_table} as book ON review.book_id = book.ID
									LEFT JOIN {$relationship_table} as r ON book.ID = r.book_id
									INNER JOIN {$term_table} as author ON (r.term_id = author.term_id AND author.type = 'author')
									WHERE `date_written` >= %s
									AND `date_written` <= %s
									GROUP BY book.ID
									ORDER BY review.date_written DESC",
				self::$start,
				self::$end
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
	 * Get Number of Books Read
	 *
	 * @todo   Not sure if rereads are tracking correctly.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return array Array including:
	 *               `total` - Total number of books *completed*.
	 *               `rereads` - Number of rereads completed.
	 *               `new` - Number of new books read.
	 */
	public function get_number_books_read() {

		global $wpdb;
		$book_table    = book_database()->books->table_name;
		$reading_table = book_database()->reading_list->table_name;

		$read = array(
			'total'   => 0,
			'rereads' => 0,
			'new'     => 0
		);

		$reading_list = book_database()->reading_list->get_entries( array(
			'number'        => - 1,
			'date_finished' => array(
				'start' => self::$startstr,
				'end'   => self::$endstr
			)
		) );

		$read['total'] = is_array( $reading_list ) ? count( $reading_list ) : 0;

		if ( is_array( $reading_list ) && ! empty( $reading_list ) ) {
			$query = $wpdb->prepare(
				"SELECT (COUNT(*) - 1) AS count, GROUP_CONCAT(date_finished SEPARATOR ',') as date_finished
					FROM $reading_table list
					INNER JOIN $book_table book on book.ID = list.book_ID
					WHERE `date_finished` < %s
					GROUP BY book_id",
				self::$end
			);

			$books_read = $wpdb->get_results( $query );
			$rereads    = 0;

			if ( is_array( $books_read ) ) {
				foreach ( $books_read as $number ) {
					if ( $number->count < 1 ) {
						continue;
					}

					$dates_finished = explode( ',', $number->date_finished );

					$this_book_rereads = 0;

					foreach ( $dates_finished as $date ) {
						if ( $date >= self::$start && $date <= self::$end ) {
							$this_book_rereads ++;
						}

						if ( $this_book_rereads >= $number->count ) {
							break;
						}
					}

					$rereads = $rereads + $this_book_rereads;
				}
			}

			$read['rereads'] = absint( $rereads );
		}

		$read['new'] = $read['total'] - $read['rereads'];

		return $read;

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
		$book_table    = book_database()->books->table_name;
		$reading_table = book_database()->reading_list->table_name;
		$pages_read    = 0;

		$query   = $wpdb->prepare(
			"SELECT pages,complete from $book_table book INNER JOIN $reading_table list ON (list.book_id = book.ID AND `date_finished` >= %s AND `date_finished` <= %s)",
			self::$start,
			self::$end
		);
		$results = $wpdb->get_results( $query );

		if ( $results ) {
			foreach ( $results as $result ) {

				$pages_read = $pages_read + round( $result->pages * ( $result->complete / 100 ) );
			}
		}

		return number_format_i18n( absint( $pages_read ) );

	}

	/**
	 * Get Number of Different Series
	 *
	 * @access public
	 * @since  1.2.1
	 * @return int
	 */
	public function get_number_different_series() {

		global $wpdb;

		$reading_table = book_database()->reading_list->table_name;
		$book_table    = book_database()->books->table_name;

		$query = $wpdb->prepare(
			"SELECT COUNT(*)
				FROM $reading_table as log 
				INNER JOIN $book_table as book on book.ID = log.book_id
				WHERE `date_finished` >= %s 
				AND `date_finished` <= %s
				AND `series_id` IS NOT NULL
				GROUP BY series_id",
			self::$start,
			self::$end
		);

		$results = $wpdb->get_results( $query );

		return is_array( $results ) ? absint( count( $results ) ) : 0;

	}

	/**
	 * Get Number of Standalones
	 *
	 * @access public
	 * @since  1.2.1
	 * @return int
	 */
	public function get_number_standalones() {

		global $wpdb;

		$reading_table = book_database()->reading_list->table_name;
		$book_table    = book_database()->books->table_name;

		$query = $wpdb->prepare(
			"SELECT COUNT(*)
				FROM $reading_table as log 
				INNER JOIN $book_table as book on book.ID = log.book_id
				WHERE `date_finished` >= %s 
				AND `date_finished` <= %s
				AND `series_id` IS NULL",
			self::$start,
			self::$end
		);

		return absint( $wpdb->get_var( $query ) );

	}

	/**
	 * Get Number of Different Authors
	 *
	 * @access public
	 * @since  1.2.1
	 * @return int
	 */
	public function get_number_different_authors() {

		global $wpdb;

		$reading_table      = book_database()->reading_list->table_name;
		$relationship_table = book_database()->book_term_relationships->table_name;
		$term_table         = book_database()->book_terms->table_name;

		$query = $wpdb->prepare(
			"SELECT COUNT(*)
				FROM $reading_table as log 
				INNER JOIN $relationship_table as r on r.book_id = log.book_id
				INNER JOIN $term_table as author on (author.term_id = r.term_id AND author.type = 'author')
				WHERE `date_finished` >= %s 
				AND `date_finished` <= %s
				GROUP BY author.term_id",
			self::$start,
			self::$end
		);

		$results = $wpdb->get_results( $query );

		return is_array( $results ) ? absint( count( $results ) ) : 0;

	}

	/**
	 * Get Average Rating
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|float
	 */
	public function get_average_rating() {

		global $wpdb;

		$reading_table = book_database()->reading_list->table_name;

		$query = $wpdb->prepare(
			"SELECT ROUND(AVG(IF(rating = 'dnf', 0, rating)), 2)
				FROM $reading_table
				WHERE `rating` IS NOT NULL
				AND `date_finished` >= %s 
				AND `date_finished` <= %s",
			self::$start,
			self::$end
		);

		$average = $wpdb->get_var( $query );

		return str_replace( '.00', '', $average );

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

		$reviews = self::$instance->query_reviews();
		$list    = array();

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
					'date'             => bdb_format_mysql_date( $review->date_written )
				);
			}
		}

		return $list;

	}

	/**
	 * Books Read but Not Reviewed
	 *
	 * @access public
	 * @since  1.1.0
	 * @return array
	 */
	public function get_read_not_reviewed() {

		$list = array();

		global $wpdb;
		$book_table         = book_database()->books->table_name;
		$reading_table      = book_database()->reading_list->table_name;
		$relationship_table = book_database()->book_term_relationships->table_name;
		$term_table         = book_database()->book_terms->table_name;

		$query = $wpdb->prepare( "SELECT DISTINCT list.ID, list.date_started, list.date_finished, list.rating,
										book.ID as book_id, book.title as book_title,
										GROUP_CONCAT(author.name SEPARATOR ', ') as author_name
									FROM {$reading_table} as list
									INNER JOIN {$book_table} as book ON list.book_id = book.ID
									LEFT JOIN {$relationship_table} as r ON book.ID = r.book_id
									INNER JOIN {$term_table} as author ON (r.term_id = author.term_id AND author.type = 'author')
									WHERE `date_finished` >= %s
									AND `date_finished` <= %s
									AND `review_id` = 0
									GROUP BY book.ID
									ORDER BY list.date_finished DESC
									LIMIT 20",
			self::$start,
			self::$end
		);

		$books = $wpdb->get_results( $query );

		if ( is_array( $books ) ) {
			foreach ( $books as $book ) {
				$rating = new BDB_Rating( $book->rating );
				$list[] = array(
					'book'           => sprintf( _x( '%s by %s', 'book title by author', 'book-database' ), $book->book_title, $book->author_name ),
					'edit_book_link' => bdb_get_admin_page_edit_book( absint( $book->book_id ) ),
					'date'           => bdb_format_mysql_date( $book->date_finished ),
					'rating'         => $rating->format( 'html_stars' ),
					'rating_class'   => sanitize_html_class( $rating->format( 'html_class' ) ),
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
		$reading_table = book_database()->reading_list->table_name;

		$query   = $wpdb->prepare( "SELECT rating, COUNT(rating) AS count FROM {$reading_table} WHERE `rating` IS NOT NULL AND `date_finished` >= %s AND `date_finished` <= %s GROUP BY rating ORDER BY rating + 0 DESC", self::$start, self::$end );
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
	 * Get Pages Breakdown
	 *
	 * Returns a table containing page ranges, along with how many books fell into that range.
	 * Example:
	 *
	 * +------------+-----------------+
	 * | page_range | number of books |
	 * +------------+-----------------+
	 * | 0-199      |               2 |
	 * | 200-399    |               6 |
	 * | 400-599    |               1 |
	 * +------------+-----------------+
	 *
	 * @param int $number_range Number of pages in each range.
	 *
	 * @access public
	 * @since  1.2.1
	 * @return array
	 */
	public function get_pages_breakdown( $number_range = 200 ) {

		global $wpdb;
		$reading_table = book_database()->reading_list->table_name;
		$book_table    = book_database()->books->table_name;

		$query = $wpdb->prepare(
			"SELECT CONCAT(%d * FLOOR(pages/%d), '-', %d * FLOOR(pages/%d) + %d) as page_range, COUNT(*) as count
				FROM $reading_table as log 
				INNER JOIN $book_table as book on book.ID = log.book_id
				WHERE `date_finished` >= %s 
				AND `date_finished` <= %s
				GROUP BY 1
				ORDER BY pages",
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range - 1 ),
			self::$start,
			self::$end
		);

		$breakdown = $wpdb->get_results( $query, ARRAY_A );

		if ( ! is_array( $breakdown ) ) {
			$breakdown = array();
		}

		return $breakdown;

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
		$reading_table      = book_database()->reading_list->table_name;

		$where = '';

		if ( $term_type && array_key_exists( $term_type, $types ) ) {
			$where .= $wpdb->prepare( " AND term.type = %s", wp_strip_all_tags( sanitize_text_field( $term_type ) ) );
		}

		$query = $wpdb->prepare( "SELECT COUNT(log.ID) as number_books, ROUND(AVG(IF(log.rating = 'dnf', 0, log.rating)), 2) as avg_rating, COUNT(review.ID) as number_reviews, term.name, term.type
									FROM {$reading_table} log
									LEFT JOIN {$review_table} review on review.ID = log.review_id
									INNER JOIN {$relationship_table} r on r.book_id = log.book_id
									INNER JOIN {$term_table} term on term.term_id = r.term_id
									WHERE `date_finished` >= %s
									AND `date_finished` <= %s
									{$where}
									GROUP BY term.type, term.name
									ORDER BY term.name ASC",
			self::$start,
			self::$end
		);

		//file_put_contents( BDB_DIR . 'log.txt', $query . "\n\n", FILE_APPEND );

		$results = $wpdb->get_results( $query );

		return $results;

	}

}