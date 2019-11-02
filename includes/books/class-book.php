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

	protected $cover_id = 0;

	protected $title = '';

	protected $index_title = '';

	protected $authors;

	protected $series_id = 0;

	protected $series_position = 0;

	protected $pub_date = '';

	protected $pages = 0;

	protected $synopsis = '';

	protected $goodreads_url = '';

	protected $links;

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
	 * Get the authors of the book
	 *
	 * This returns an array of `Author` objects.
	 *
	 * @param array $args Query args to override the defaults.
	 *
	 * @return Author[]|array
	 */
	public function get_authors( $args = array() ) {

		if ( ! isset( $this->authors ) ) {
			$this->authors = get_attached_book_authors( $this->get_id(), $args );
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
		return $this->series_position ?? null;
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
	 *                          Unlike other dates, this DOES NOT convert the date
	 *                          to local time. We keep it in UTC always.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_pub_date( $formatted = false, $format = '' ) {

		if ( empty( $this->pub_date ) || ! $formatted ) {
			return $this->pub_date;
		}

		$format = ! empty( $format ) ? $format : get_option( 'date_format' );

		return date( $format, strtotime( $this->pub_date ) );

	}

	/**
	 * Get the number of pages in the book
	 *
	 * @return int
	 */
	public function get_pages() {
		return ! empty( $this->pages ) ? absint( $this->pages ) : null;
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
	 * Get the links associated with this book
	 *
	 * @return Book_Link[]
	 */
	public function get_links() {

		if ( ! isset( $this->links ) ) {
			$this->links = get_book_links( array(
				'book_id' => $this->get_id(),
				'number'  => 30
			) );
		}

		return $this->links;

	}

	/**
	 * Get the average rating from all reading logs associated with this book
	 *
	 * @return int|float|null
	 */
	public function get_average_rating() {

		global $wpdb;

		$log_table = book_database()->get_table( 'reading_log' )->get_table_name();

		$query   = $wpdb->prepare( "SELECT ROUND( AVG( rating ), 2 ) FROM {$log_table} WHERE book_id = %d AND rating IS NOT NULL", $this->get_id() );
		$average = $wpdb->get_var( $query );

		/**
		 * Filters the average rating for a book.
		 *
		 * @param int|float|null $average Average rating.
		 * @param int            $book_id ID of the book.
		 * @param Book           $this    Book object.
		 */
		return apply_filters( 'book-database/book/get/average-rating', $average, $this->get_id(), $this );

	}

	/**
	 * Whether or not the book has a term attached.
	 *
	 * @param string|int $term_name_or_id Term name or ID.
	 * @param string     $taxonomy        Taxonomy slug.
	 *
	 * @return bool
	 */
	public function has_term( $term_name_or_id, $taxonomy ) {

		$args = array();

		if ( is_numeric( $term_name_or_id ) ) {
			$args['fields'] = 'id';
		} else {
			$args['fields'] = 'name';
		}

		$terms = get_attached_book_terms( $this->get_id(), $taxonomy, $args );

		return in_array( $term_name_or_id, $terms );

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
			'authors'         => $this->get_authors(),
			'series_id'       => $this->get_series_id(),
			'series_name'     => $this->get_series_name(),
			'series_position' => $this->get_series_position(),
			'pub_date'        => $this->get_pub_date(),
			'pages'           => $this->get_pages(),
			'synopsis'        => $this->get_synopsis(),
			'goodreads_url'   => $this->get_goodreads_url(),
			'links'           => $this->get_links(),
			'terms'           => array(),
			'date_created'    => $this->get_date_created(),
			'date_modified'   => $this->get_date_modified()
		);

		// Attach all terms.
		foreach ( get_book_taxonomies( array( 'field' => 'slug' ) ) as $taxonomy ) {
			$data['terms'][ $taxonomy ] = get_attached_book_terms( $this->get_id(), $taxonomy );
		}

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