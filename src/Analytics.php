<?php
/**
 * Reading & Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Analytics
 * @package Book_Database
 */
class Analytics {

	/**
	 * @var string Start date.
	 */
	protected $start_date = '';

	/**
	 * @var string End date.
	 */
	protected $end_date = '';

	/**
	 * @var array Arguments.
	 */
	protected $args = array();

	/**
	 * @var \wpdb
	 */
	protected $wpdb;

	/**
	 * @var array Database table names.
	 */
	protected $tables = array();

	/**
	 * @var null|array
	 */
	protected $number_books_read_breakdown = null;

	/**
	 * Reviews written during this period
	 *
	 * @var null|int
	 */
	protected $reviews = null;

	/**
	 * Analytics constructor.
	 *
	 * @param string $start_date
	 * @param string $end_date
	 */
	public function __construct( $start_date, $end_date, $args = array() ) {

		global $wpdb;

		$this->start_date = $start_date;
		$this->end_date   = $end_date;
		$this->args       = wp_parse_args( $args, array(
			'rating_format' => null
		) );
		$this->wpdb       = $wpdb;
		$this->tables     = array(
			'authors'     => book_database()->get_table( 'authors' )->get_table_name(),
			'author_r'    => book_database()->get_table( 'book_author_relationships' )->get_table_name(),
			'term_r'      => book_database()->get_table( 'book_term_relationships' )->get_table_name(),
			'book_terms'  => book_database()->get_table( 'book_terms' )->get_table_name(),
			'books'       => book_database()->get_table( 'books' )->get_table_name(),
			'editions'    => book_database()->get_table( 'editions' )->get_table_name(),
			'reading_log' => book_database()->get_table( 'reading_log' )->get_table_name(),
			'reviews'     => book_database()->get_table( 'reviews' )->get_table_name(),
			'series'      => book_database()->get_table( 'series' )->get_table_name(),
		);

	}

	/**
	 * Log the performed query
	 *
	 * This only actually logs if WP_DEBUG is enabled.
	 *
	 * @param string $query  MySQL query.
	 * @param string $method Method name.
	 */
	protected function log( $query, $method ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( "%s:\n%s", $method, $query ) );
		}
	}

	/**
	 * Get a breakdown of how books were read
	 *
	 * Returns an array of `finished`, `dnf`, `rereads`, and `new`.
	 *
	 * @see Analytics\Books_Finished_Count
	 * @see Analytics\Books_DNF_Count
	 *
	 * @return array
	 */
	public function get_number_books_read_breakdown() {

		if ( is_array( $this->number_books_read_breakdown ) ) {
			return $this->number_books_read_breakdown;
		}

		$counts = array(
			'finished' => 0,
			'dnf'      => 0,
			'rereads'  => 0,
			'new'      => 0
		);

		/**
		 * Number of books finished.
		 */
		$query = $this->wpdb->prepare(
			"SELECT COUNT(*) FROM {$this->tables['reading_log']}
				WHERE date_finished >= %s
            	AND date_finished <= %s
                AND date_finished IS NOT NULL
                AND percentage_complete >= 1",
			$this->start_date, $this->end_date
		);

		$this->log( $query, __METHOD__ . '\finished' );

		$counts['finished'] = absint( $this->wpdb->get_var( $query ) );

		/**
		 * Number of books DNF
		 */
		$query = $this->wpdb->prepare(
			"SELECT COUNT(*)
				FROM {$this->tables['reading_log']}
				WHERE date_started >= %s
				AND date_finished <= %s
				AND date_finished IS NOT NULL
				AND percentage_complete < 1",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ . '\dnf' );

		$counts['dnf'] = absint( $this->wpdb->get_var( $query ) );

		/**
		 * Count rereads
		 */
		$query = $this->wpdb->prepare(
			"SELECT ( COUNT(*) - 1 ) AS count, GROUP_CONCAT( date_finished SEPARATOR ',' ) AS date_finished, book_id
				FROM {$this->tables['reading_log']} AS log
				INNER JOIN {$this->tables['books']} AS book ON book.id = log.book_id
				WHERE `date_finished` < %s
				AND `date_finished` IS NOT NULL 
				GROUP BY book_id",
			$this->end_date
		);

		$this->log( $query, __METHOD__ . '\rereads' );

		$books_read = $this->wpdb->get_results( $query );

		if ( ! empty( $books_read ) ) {
			foreach ( $books_read as $book_read ) {
				if ( $book_read->count < 1 ) {
					continue;
				}

				$dates_finished    = explode( ',', $book_read->date_finished );
				$this_book_rereads = 0;

				foreach ( $dates_finished as $date_finished ) {
					if ( $date_finished >= $this->start_date && $date_finished <= $this->end_date ) {
						$this_book_rereads ++;
					}

					if ( $this_book_rereads >= $book_read->count ) {
						break;
					}
				}

				$counts['rereads'] += $this_book_rereads;
			}
		}

		$counts['new'] = ( $counts['finished'] + $counts['dnf'] ) - $counts['rereads'];

		return $counts;

	}

	/**
	 * Returns the number of books that have been fully read/finished.
	 *
	 * @return int
	 */
	public function get_number_books_finished() {

		$breakdown = $this->get_number_books_read_breakdown();

		return absint( $breakdown['finished'] );

	}

	/**
	 * Returns the number of books that were marked as DNF
	 *
	 * @return int
	 */
	public function get_number_dnf() {

		$breakdown = $this->get_number_books_read_breakdown();

		return absint( $breakdown['dnf'] );

	}

	/**
	 * Get the number of new books that were read
	 *
	 * @return int
	 */
	public function get_number_new_books() {

		$breakdown = $this->get_number_books_read_breakdown();

		return absint( $breakdown['new'] );

	}

	/**
	 * Get the number of rereads
	 *
	 * @return int
	 */
	public function get_number_rereads() {

		$breakdown = $this->get_number_books_read_breakdown();

		return absint( $breakdown['rereads'] );

	}

	/**
	 * Get the number of pages read
	 *
	 * @see \Book_Database\Analytics\Datasets\Pages_Read
	 *
	 * @return int
	 */
	public function get_number_pages_read() {

		$query = $this->wpdb->prepare(
			"SELECT SUM(pages * percentage_complete) as pages_read
				FROM {$this->tables['books']} AS book
				INNER JOIN {$this->tables['reading_log']} AS log ON( log.book_id = book.id )
				WHERE date_finished >= %s
				AND date_finished <= %s
				AND date_finished IS NOT NULL",
			$this->start_date, $this->end_date
		);

		$this->log( $query, __METHOD__ );

		$result = $this->wpdb->get_var( $query );

		return round( absint( $result ) );

	}

	/**
	 * Calculate the number of books on track to be read in a given period
	 *
	 * @return int
	 */
	public function get_reading_track() {

		try {

			$books_read = $this->get_number_books_finished();

			// If end date is in the past, return books read.
			if ( time() > strtotime( $this->end_date ) ) {
				return $books_read;
			}

			$now        = new \DateTime();
			$start_date = new \DateTime( $this->start_date );
			$end_date   = new \DateTime( $this->end_date );

			// Calculate books read per day so far.
			$days_in_period = $now->diff( $start_date )->days;
			$books_per_day  = ( $days_in_period > 0 ) ? $books_read / $days_in_period : 0;

			// Based on books per day, calculate how many we'll read in the remaining days.
			$remaining_days = $end_date->diff( $now )->days;
			$left_to_read   = $books_per_day * $remaining_days;

			return round( $left_to_read + $books_read );

		} catch ( \Exception $e ) {
			return null;
		}

	}

	/**
	 * Query for reviews written during this period
	 *
	 * @return object[]
	 */
	public function query_reviews() {

		if ( ! is_null( $this->reviews ) ) {
			return $this->reviews;
		}

		$query = $this->wpdb->prepare(
			"SELECT DISTINCT review.id, review.date_written, log.rating AS rating, book.id AS book_id, book.title AS book_title, GROUP_CONCAT(author.name SEPARATOR ', ') AS author_name
				FROM {$this->tables['reviews']} AS review 
				LEFT JOIN {$this->tables['reading_log']} AS log ON ( review.reading_log_id = log.id )
				INNER JOIN {$this->tables['books']} AS book ON ( review.book_id = book.id )
				LEFT JOIN {$this->tables['author_r']} AS ar ON ( book.id = ar.book_id )
				INNER JOIN {$this->tables['authors']} AS author ON ( ar.author_id = author.id )
				WHERE date_written >= %s 
				AND date_written <= %s 
				GROUP BY book.id
				ORDER BY review.date_written DESC",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$this->reviews = $this->wpdb->get_results( $query );

		return $this->reviews;

	}

	/**
	 * Get the number of reviews
	 *
	 * @return int
	 */
	public function get_number_reviews() {

		$reviews = $this->query_reviews();

		return is_array( $reviews ) ? count( $reviews ) : 0;

	}

	/**
	 * Get the average star rating
	 *
	 * @return float|int
	 */
	public function get_avg_rating() {

		$query = $this->wpdb->prepare(
			"SELECT ROUND( AVG( rating ), 2 )
			FROM {$this->tables['reading_log']}
			WHERE rating IS NOT NULL 
			AND date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$average = $this->wpdb->get_var( $query );

		return $average * 1;

	}

	/**
	 * Count the number of different series read
	 *
	 * @return int
	 */
	public function get_number_different_series() {

		$query = $this->wpdb->prepare(
			"SELECT COUNT( DISTINCT series_id )
			FROM {$this->tables['reading_log']} AS log 
			INNER JOIN {$this->tables['books']} AS book ON ( book.id = log.book_id )
			WHERE date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL 
			AND series_id IS NOT NULL",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$number = $this->wpdb->get_var( $query );

		return absint( $number );

	}

	/**
	 * Count the number of standalones read
	 *
	 * @return int
	 */
	public function get_number_standalones() {

		$query = $this->wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$this->tables['reading_log']} AS log 
			INNER JOIN {$this->tables['books']} AS book ON ( book.id = log.book_id )
			WHERE date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL 
			AND series_id IS NULL",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$number = $this->wpdb->get_var( $query );

		return absint( $number );

	}

	/**
	 * Get the number of different authors
	 *
	 * @return int
	 */
	public function get_number_authors() {

		$query = $this->wpdb->prepare(
			"SELECT COUNT( DISTINCT ar.author_id )
			FROM {$this->tables['author_r']} AS ar 
			INNER JOIN {$this->tables['reading_log']} AS log ON ( ar.book_id = log.book_id )
			WHERE date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$number = $this->wpdb->get_var( $query );

		return absint( $number );

	}

	/**
	 * Get rating breakdown
	 *
	 * Returns an array of arrays, containing each available rating and the associated number of books.
	 */
	public function get_rating_breakdown() {

		$query = $this->wpdb->prepare(
			"SELECT rating, COUNT( IFNULL( rating, 1 ) ) AS count
			FROM {$this->tables['reading_log']} AS log 
			WHERE date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL 
			GROUP BY rating
			ORDER BY rating DESC",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$results     = $this->wpdb->get_results( $query );
		$final_array = $temp_array = array();

		foreach ( get_available_ratings() as $rating => $label ) {
			$temp_array[ $rating ] = 0;
		}

		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$key                = ( null === $result->rating ) ? 'none' : (string) ( $result->rating * 1 );
				$temp_array[ $key ] = absint( $result->count );
			}
		}

		foreach ( $temp_array as $key => $value ) {
			$rating        = new Rating( $key );
			$final_array[] = array(
				'rating'       => 'none' === $key ? 'none' : $rating->format( $this->args['rating_format'] ),
				'number_books' => $value
			);
		}

		return $final_array;

	}

	/**
	 * Get pages breakdown
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
	 * @return array
	 */
	public function get_pages_breakdown( $number_range = 200 ) {

		$query = $this->wpdb->prepare(
			"SELECT CONCAT( %d * FLOOR( pages/%d ), '-', %d * FLOOR( pages/%d ) + %d ) AS page_range, COUNT(*) AS number_books
			FROM {$this->tables['reading_log']} AS log 
			INNER JOIN {$this->tables['books']} AS book ON ( book.id = log.book_id )
			WHERE date_finished >= %s
			AND date_finished <= %s 
			AND date_finished IS NOT NULL 
			AND book.pages IS NOT NULL 
			GROUP BY 1
			ORDER BY pages",
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range - 1 ),
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __NAMESPACE__ );

		$breakdown = $this->wpdb->get_results( $query, ARRAY_A );

		if ( ! is_array( $breakdown ) ) {
			$breakdown = array();
		}

		return $breakdown;

	}

	/**
	 * Get taxonomy breakdown
	 *
	 * For each term: number of books read, average rating, and term name.
	 *
	 * @param string $taxonomy Taxonomy slug
	 *
	 * @return array|object|null
	 */
	public function get_taxonomy_breakdown( $taxonomy ) {

		$query = $this->wpdb->prepare(
			"SELECT COUNT( log.id ) AS number_books_read, ROUND( AVG( log.rating ), 2 ) AS average_rating, COUNT( review.id ) AS number_reviews, term.name AS term_name
			FROM {$this->tables['reading_log']} AS log 
			LEFT JOIN {$this->tables['reviews']} AS review ON ( review.reading_log_id = log.id )
			INNER JOIN {$this->tables['term_r']} AS tr ON ( tr.book_id = log.book_id )
			INNER JOIN {$this->tables['book_terms']} AS term ON ( term.id = tr.term_id )
			WHERE date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL 
			AND term.taxonomy = %s 
			GROUP BY term.taxonomy, term.name 
			ORDER BY term.name ASC",
			$this->start_date,
			$this->end_date,
			$taxonomy
		);

		$this->log( $query, __NAMESPACE__ );

		$results = $this->wpdb->get_results( $query );

		// Format rating.
		foreach ( $results as $key => $result ) {
			$rating                          = new Rating( $result->average_rating );
			$results[ $key ]->average_rating = $rating->format( $this->args['rating_format'] );
		}

		return $results;

	}

	/**
	 * Get a list of reviews written
	 *
	 * Limited to 20 results.
	 *
	 * @return object[]|array
	 */
	public function get_reviews_written() {

		$reviews = $this->query_reviews();

		if ( ! is_array( $reviews ) ) {
			return null;
		}

		// Limit results to 20.
		if ( count( $reviews ) > 20 ) {
			$reviews = array_slice( $reviews, 0, 20 );
		}

		foreach ( $reviews as $key => $review ) {
			$rating                        = new Rating( $review->rating );
			$reviews[ $key ]->rating       = $rating->format_html_stars();
			$reviews[ $key ]->rating_class = $rating->format_html_class();
		}

		return $reviews;

	}

	/**
	 * Get a list of books that have been read but not reviewed
	 *
	 * Limited to 20 results.
	 *
	 * @return object[]|array
	 */
	public function get_read_not_reviewed() {

		$query = $this->wpdb->prepare(
			"SELECT DISTINCT log.id, log.date_started, log.date_finished, log.rating, log.percentage_complete, book.id AS book_id, book.title AS book_title, GROUP_CONCAT(author.name SEPARATOR ', ') AS author_name
			FROM {$this->tables['reading_log']} AS log 
			INNER JOIN {$this->tables['books']} AS book ON ( log.book_id = book.id )
			LEFT JOIN {$this->tables['author_r']} AS ar ON ( log.book_id = ar.book_id )
			INNER JOIN {$this->tables['authors']} AS author ON ( ar.author_id = author.id )
			LEFT JOIN {$this->tables['reviews']} AS review ON ( book.id = review.book_id )
			WHERE date_finished >= %s 
			AND date_finished <= %s 
			AND date_finished IS NOT NULL 
			AND review.book_id IS NULL
			GROUP BY book.id 
			ORDER BY log.date_finished DESC 
			LIMIT 20",
			$this->start_date,
			$this->end_date
		);

		$this->log( $query, __METHOD__ );

		$books = $this->wpdb->get_results( $query );

		if ( ! is_array( $books ) ) {
			return null;
		}

		foreach ( $books as $key => $book ) {
			$rating                      = new Rating( $book->rating );
			$books[ $key ]->rating       = $rating->format_html_stars();
			$books[ $key ]->rating_class = $rating->format_html_class();
		}

		return $books;

	}

}
