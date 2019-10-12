<?php
/**
 * Book
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book
 * @package Book_Database
 */
class Book extends Base_Object {

	protected $authors;

	protected $cover_id = 0;

	protected $title = '';

	protected $index_title = '';

	protected $series_id = 0;

	protected $series_position = 0;

	protected $pub_date = '';

	protected $pages = 0;

	protected $synopsis = '';

	protected $goodreads_url = '';

	protected $buy_link = '';

	/**
	 * Get the authors of the book
	 *
	 * This returns an array of `Book_Term` objects.
	 *
	 * @param array $args Query args to override the defaults.
	 *
	 * @return Book_Term[]|array
	 */
	public function get_authors( $args = array() ) {

		if ( ! isset( $this->authors ) ) {
			$this->authors = get_book_authors( $this->get_id(), $args );
		}

		return $this->authors;

	}

	/**
	 * Get the author names
	 *
	 * @param bool $implode True to return a comma-separated list, false to return an array.
	 *
	 * @return array|string
	 */
	public function get_author_names( $implode = false ) {

		$author_names = array();
		$author_terms = $this->get_authors();

		if ( $author_terms ) {
			foreach ( $author_terms as $author_term ) {
				$author_names[] = $author_term->get_name();
			}
		}

		if ( $implode ) {
			$author_names = implode( ', ', $author_names );
		}

		return $author_names;

	}

	/**
	 * Get the attachment ID for the cover image
	 *
	 * @return int
	 */
	public function get_cover_id() {
		return absint( $this->cover_id );
	}

	/**
	 * Get the cover image URL
	 *
	 * @param string $size Desired image size.
	 *
	 * @return string
	 */
	public function get_cover_url( $size = 'full' ) {

		$url      = '';
		$cover_id = $this->get_cover_id();

		if ( ! empty( $cover_id ) ) {
			$url = wp_get_attachment_image_url( $cover_id, $size );
		}

		return apply_filters( 'book-database/book/get/cover_url', $url, $cover_id, $this );

	}

	/**
	 * Get the cover image HTML.
	 *
	 * @param string|array $size Desired image size.
	 * @param array        $args Arguments to use in `wp_get_attachment_image()`.
	 *
	 * @return string
	 */
	public function get_cover( $size = 'full', $args = array() ) {

		$image    = '';
		$cover_id = $this->get_cover_id();

		if ( $cover_id ) {
			$image = wp_get_attachment_image( absint( $cover_id ), $size, false, $args );
		}

		return apply_filters( 'book-database/book/get/cover', $image, $cover_id, $this );

	}

	/**
	 * Get the title of the book
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get the index-friendly title
	 *
	 * This moves "The", "An", and "A" to the end of the title. Example:
	 * `Binding, The`
	 *
	 * @return string
	 */
	public function get_index_title() {
		return $this->index_title;
	}

	/**
	 * Get the ID of the series this book is in
	 *
	 * @return int
	 */
	public function get_series_id() {
		return absint( $this->series_id );
	}

	/**
	 * Get the position in the series
	 *
	 * @return int|float
	 */
	public function get_series_position() {
		return $this->series_position;
	}

	/**
	 * Get the name of the series
	 *
	 * @return string|false Series name on success, false on failure.
	 */
	public function get_series_name() {

		$series = get_book_series_by( 'id', $this->get_series_id() );

		if ( $series instanceof Series ) {
			return $series->get_name();
		}

		return false;

	}

	/**
	 * Get the book's publication date
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_pub_date( $formatted = false, $format = '' ) {
		return ( ! empty( $this->pub_date ) && $formatted ) ? format_date( $this->pub_date, $format ) : $this->pub_date;
	}

	/**
	 * Get the number of pages in the book
	 *
	 * @return int
	 */
	public function get_pages() {
		return absint( $this->pages );
	}

	/**
	 * Get the synopsis
	 *
	 * @return string
	 */
	public function get_synopsis() {
		return $this->synopsis;
	}

	/**
	 * Get the Goodreads URL
	 *
	 * @return string
	 */
	public function get_goodreads_url() {
		return $this->goodreads_url;
	}

	/**
	 * Get the purchase link
	 *
	 * @return string
	 */
	public function get_buy_link() {
		return $this->buy_link;
	}

	/**
	 * Returns all data associated with a book
	 *
	 * @return array
	 */
	public function get_data() {

		$data = array(
			'id'              => $this->get_id(),
			'cover_id'        => $this->get_cover_id(),
			'cover_url'       => $this->get_cover_url( 'full' ),
			'title'           => $this->get_title(),
			'index_title'     => $this->get_index_title(),
			'author'          => '', // @todo
			'series_id'       => $this->get_series_id(),
			'series_name'     => '', // @todo
			'series_position' => $this->get_series_position(),
			'pub_date'        => $this->get_pub_date(),
			'pages'           => $this->get_pages(),
			'synopsis'        => $this->get_synopsis(),
			'goodreads_url'   => $this->get_goodreads_url(),
			'buy_link'        => $this->get_buy_link(),
			'date_created'    => $this->get_date_created(),
			'date_modified'   => $this->get_date_modified()
		);

		/**
		 * Filters the data.
		 *
		 * @param array $data
		 * @param int   $book_id
		 * @param Book  $this
		 */
		return apply_filters( 'book-database/book/get/data', $data, $this->get_id(), $this );

	}

}