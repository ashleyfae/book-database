<?php
/**
 * Series Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Series
 * @package Book_Database
 */
class Series extends Base_Object {

	protected $name = '';

	protected $slug = '';

	protected $description = '';

	protected $number_books = 0;

	/**
	 * Get the name of the series
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the series slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the series description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the number of books in the series
	 *
	 * Note: This is not the number of books present in the database with this series, but
	 * rather the number of books PLANNED to be in the series. So if the series is a trilogy
	 * but there's only one book in the database, this would return `3` because there are 3
	 * books planned in the series. It's the length of the series.
	 *
	 * @return int
	 */
	public function get_number_books() {

		if ( empty( $this->number_books ) ) {
			$this->number_books = count_books( array( 'series_id' => $this->get_id() ) );
		}

		return absint( $this->number_books );

	}

	/**
	 * Get the number of books in this series that have been read
	 *
	 * @todo
	 *
	 * @param array $args
	 *
	 * @return int
	 */
	public function get_number_books_read( $args = array() ) {
		return absint( 0 );
	}

	/**
	 * Get the books in this series
	 *
	 * @param array $args
	 *
	 * @return Book[]
	 */
	public function get_books_in_series( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'series_id' => $this->get_id(),
			'number'    => 50
		) );

		return get_books( $args );

	}

	/**
	 * Get the average rating of all books in this series
	 *
	 * @todo
	 */
	public function get_average_rating() {

		global $wpdb;

		$log_table  = book_database()->get_table( 'reading_log' )->get_table_name();
		$book_table = book_database()->get_table( 'books' )->get_table_name();

		$query   = $wpdb->prepare( "SELECT ROUND( AVG( rating ), 2 ) FROM {$log_table} log INNER JOIN {$book_table} b ON log.book_id = b.id WHERE series_id = %d AND rating IS NOT NULL", $this->get_id() );
		$average = $wpdb->get_var( $query );

		return $average;

	}

}