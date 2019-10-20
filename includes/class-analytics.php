<?php
/**
 * Reading & Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
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
	 * Analytics constructor.
	 *
	 * @param string $start_date
	 * @param string $end_date
	 */
	public function __construct( $start_date, $end_date ) {

		global $wpdb;

		$this->start_date = $start_date;
		$this->end_date   = $end_date;
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

	protected function log( $query, $method ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( "%s:\n%s", $method, $query ) );
		}
	}

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
		$query = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$this->tables['reading_log']} WHERE date_started >= %s AND date_finished <= %s AND date_finished IS NOT NULL AND percentage_complete >= 1", $this->start_date, $this->end_date );

		$this->log( $query, __METHOD__ . '\finished' );

		$counts['finished'] = absint( $this->wpdb->get_var( $query ) );

		/**
		 * Number of books DNF
		 */
		$query = $this->wpdb->prepare( "SELECT COUNT(*) FROM {$this->tables['reading_log']} WHERE date_started >= %s AND date_finished <= %s AND date_finished IS NOT NULL AND percentage_complete < 1", $this->start_date, $this->end_date );

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

		$this->log( var_export( $books_read, true ), __METHOD__ );

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

	public function get_number_new_books() {

		$breakdown = $this->get_number_books_read_breakdown();

		return absint( $breakdown['new'] );

	}

	public function get_number_rereads() {

		$breakdown = $this->get_number_books_read_breakdown();

		return absint( $breakdown['rereads'] );

	}

	public function get_number_pages_read() {

	}

}