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
	 * BDB_Series constructor.
	 *
	 * @param int|object|array|string $id_or_name Series ID, object/array or name.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct( $id_or_name = 0 ) {

		if ( is_object( $id_or_name ) || is_array( $id_or_name ) ) {
			$series = $id_or_name;
		} elseif ( is_numeric( $id_or_name ) ) {
			$series = book_database()->series->get_series_by( 'ID', $id_or_name );
		} else {
			$series = book_database()->series->get_series_by( 'name', $id_or_name );
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

}