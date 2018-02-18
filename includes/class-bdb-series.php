<?php

/**
 * Series Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_Series
 *
 * @since 1.0
 */
class BDB_Series {

	/**
	 * Series ID
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $ID = 0;

	/**
	 * Series name
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $name;

	/**
	 * Series slug
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $slug;

	/**
	 * Series description
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $description;

	/**
	 * Number of books in the series
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $number_books = null;

	/**
	 * Books in this series
	 *
	 * @see    BDB_Series::get_books()
	 *
	 * @var array|false
	 * @access protected
	 * @since  1.0
	 */
	protected $books;

	/**
	 * Average rating for all books in this series
	 *
	 * @see    BDB_Series::get_average_rating()
	 *
	 * @var float|int
	 * @access protected
	 * @since  1.0
	 */
	protected $average_rating;

	/**
	 * BDB_Series constructor.
	 *
	 * @param int|object|array|string $id_or_name Series ID, object/array or name.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct( $id_name_or_object = 0 ) {

		if ( is_object( $id_name_or_object ) || is_array( $id_name_or_object ) ) {
			$series = $id_name_or_object;
		} elseif ( is_numeric( $id_name_or_object ) ) {
			$series = book_database()->series->get_series_by( 'ID', $id_name_or_object );
		} else {
			$series = book_database()->series->get_series_by( 'name', $id_name_or_object );
		}

		if ( empty( $series ) ) {
			return;
		}

		$this->setup_series( $series );

	}

	/**
	 * Setup series
	 *
	 * @param object|array $series Row from the database.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool
	 */
	public function setup_series( $series ) {

		if ( ! is_object( $series ) && ! is_array( $series ) ) {
			return false;
		}

		foreach ( $series as $key => $value ) {

			$this->$key = $value;

		}

		// We absolutely need an ID. Otherwise nothing works.
		if ( ! empty( $this->ID ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get all books in this series
	 *
	 * @access public
	 * @since  1.0
	 * @return array|false Array of books or false if none.
	 */
	public function get_books() {

		if ( ! isset( $this->books ) ) {
			$query = new BDB_Book_Query( array(
				'series_id' => $this->ID,
				'orderby'   => 'series_position',
				'order'     => 'ASC'
			) );
			$query->query();
			$this->books = $query->get_books();
		}

		return apply_filters( 'book-database/series/get-books', $this->books, $this->ID, $this );

	}

	/**
	 * Get average rating of all books in the series
	 *
	 * @access public
	 * @since  1.0
	 * @return float|int
	 */
	public function get_average_rating() {

		if ( ! isset( $this->average_rating ) ) {
			$books                = $this->get_books();
			$total_ratings        = 0;
			$total_books          = 0;
			$this->average_rating = 0;

			if ( is_array( $books ) ) {
				foreach ( $books as $book ) {
					$book_average = $book->get_average_rating();

					if ( ! is_null( $book_average ) ) {
						$total_ratings = $total_ratings + $book_average;
						$total_books ++;
					}
				}

				if ( $total_books > 0 ) {
					$this->average_rating = round( ( $total_ratings / $total_books ), 2 );
				}
			}
		}

		return apply_filters( 'book-database/series/average-rating', $this->average_rating, $this->ID, $this );

	}

	/**
	 * Gets the number of books in the series that have been read
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function get_number_books_read() {

		global $wpdb;

		$reading_table = book_database()->reading_list->table_name;
		$books_table   = book_database()->books->table_name;

		$query = "SELECT DISTINCT book_id FROM $reading_table log
				  INNER JOIN $books_table as book on (book.ID = log.book_id AND book.series_id = %d)
				  WHERE log.date_finished IS NOT NULL
				  GROUP BY book.ID";

		$book_ids = $wpdb->get_results( $wpdb->prepare( $query, $this->ID ) );

		return is_array( $book_ids ) ? count( $book_ids ) : 0;

	}

	/**
	 * Gets the total number of books in the series
	 *
	 * This uses the database column if populated. If not, it counts how many books in the
	 * series have been entered in the library.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function get_number_books() {

		if ( ! empty( $this->number_books ) ) {
			return $this->number_books;
		}

		$count = book_database()->books->count( array( 'series_id' => $this->ID ) );

		return $count;

	}

}