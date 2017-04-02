<?php

/**
 * Book Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_Book
 *
 * @since 1.0.0
 */
class BDB_Book {

	/**
	 * ID of the book.
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $ID;

	/**
	 * Cover image attachment ID.
	 *
	 * @var int
	 * @access private
	 * @since  1.0.0
	 */
	private $cover;

	/**
	 * Title of the book.
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $title;

	/**
	 * Title used for ordering in indexes.
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $index_title;

	/**
	 * Author of the book
	 *
	 * Array with keys as term IDs and values as author names.
	 *
	 * @var array
	 * @access private
	 * @since  1.0.0
	 */
	private $author;

	/**
	 * Series ID
	 *
	 * @var int|null
	 * @access private
	 * @since  1.0.0
	 */
	private $series_id;

	/**
	 * Position in the series
	 *
	 * @var string|null
	 * @access private
	 * @since  1.0.0
	 */
	private $series_position;

	/**
	 * Publication date
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $pub_date;

	/**
	 * Number of pages
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $pages;

	/**
	 * Synopsis
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $synopsis;

	/**
	 * Goodreads URL
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $goodreads_url;

	/**
	 * Buy Link
	 *
	 * @var string
	 * @access private
	 * @since  1.0.0
	 */
	private $buy_link;

	/**
	 * Faux Rating
	 *
	 * @var int|string|null
	 * @access private
	 * @since  1.0.0
	 */
	private $rating;

	/**
	 * All terms associated with the book, grouped by type
	 *
	 * @var array
	 * @access private
	 * @since  1.0.0
	 */
	private $terms;

	/**
	 * BDB_Book constructor.
	 *
	 * @param int|object|array $book_id Book ID to fetch from database or a prepared object in database format.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function __construct( $book_id ) {

		$book = ( is_object( $book_id ) || is_array( $book_id ) ) ? $book_id : book_database()->books->get_book( $book_id );

		if ( empty( $book ) || ( ! is_object( $book ) && ! is_array( $book ) ) ) {
			return false;
		}

		return $this->setup_book( $book );

	}

	/**
	 * Setup Book
	 *
	 * Given the book data, let's set up the variables.
	 *
	 * @param object|false $book Book object from the database.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool Whether or not the set up was successful.
	 */
	private function setup_book( $book ) {

		if ( ! is_object( $book ) && ! is_array( $book ) ) {
			return false;
		}

		foreach ( $book as $key => $value ) {

			$this->$key = $value;

		}

		// We absolutely need an ID. Otherwise nothing works.
		if ( ! empty( $this->ID ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property.
	 *
	 * @param string $key Property to retrieve.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return mixed
	 */
	public function __get( $key ) {

		// Not a valid book - always false.
		if ( ! $this->ID ) {
			return false;
		}

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'ubb-book-invalid-property', sprintf( __( 'Can\'t get property %s', 'book-database' ), $key ) );
		}

	}

	/**
	 * Create a Book
	 *
	 * @param array $data
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false Book ID if success, or false if failure.
	 */
	public function create( $data = array() ) {

		if ( $this->ID != 0 || empty( $data ) ) {
			return false;
		}

		$args = $data;
		$args = $this->sanitize_columns( $args );

		if ( empty( $args['title'] ) ) {
			return false;
		}

		/**
		 * Fires before a book is created.
		 *
		 * @param array $args Contains book information.
		 */
		do_action( 'book-database/book/pre-create', $args );

		$created = false;

		// The DB class 'add' implies an update if the customer asked to be created already exists.
		$book_id = book_database()->books->add( $data );

		if ( $book_id ) {

			// We've successfully added/updated the customer, reset the class vars with the new data.
			$book = book_database()->books->get_book( $book_id );

			$this->setup_book( $book );

			$created = $this->ID;

		}

		/**
		 * Fires after a book is created.
		 *
		 * @param int   $created If created successfully, the book ID. Defaults to false.
		 * @param array $args    Contains book information.
		 */
		do_action( 'book-database/book/post-create', $created, $args );

		return $created;

	}

	/**
	 * Update a Book
	 *
	 * @param array $data Array of data attributes for a book (checked via whitelist).
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool Whether or not the update was successful.
	 */
	public function update( $data = array() ) {

		if ( empty( $data ) ) {
			return false;
		}

		$data = $this->sanitize_columns( $data );

		/**
		 * Firest before a book is updated.
		 *
		 * @param int   $this ->id ID of the book being updated.
		 * @param array $data Contains new book information.
		 */
		do_action( 'book-database/book/pre-update', $this->ID, $data );

		$updated = false;

		if ( book_database()->books->update( $this->ID, $data ) ) {

			$book = book_database()->books->get_book( $this->ID );
			$this->setup_book( $book );

			$updated = true;

		}

		/**
		 * Firest after a book is updated.
		 *
		 * @param bool  $updated Whether or not the update was successful.
		 * @param int   $ID      ID of the book being updated.
		 * @param array $data    Contains new book information.
		 */
		do_action( 'book-database/book/post-update', $updated, $this->ID, $data );

		return $updated;

	}

	/**
	 * Get Meta
	 *
	 * Retrieve book meta field for a book.
	 *
	 * @param string $meta_key The meta key to retrieve.
	 * @param bool   $single   Whether or not to return a single value.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return book_database()->book_meta->get_meta( $this->ID, $meta_key, $single );
	}

	/**
	 * Add Meta
	 *
	 * Add meta data field to a book.
	 *
	 * @param string $meta_key   Key to add.
	 * @param mixed  $meta_value Value to add.
	 * @param bool   $unique     Whether the same key should not be added.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool False for failure, true for success.
	 */
	public function add_meta( $meta_key = '', $meta_value, $unique = false ) {
		return book_database()->book_meta->add_meta( $this->ID, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update Meta
	 *
	 * Update book meta field based on book ID.
	 *
	 * @param string $meta_key   Key to update.
	 * @param mixed  $meta_value New value.
	 * @param string $prev_value Optional. Previous value to check before updating.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function update_meta( $meta_key = '', $meta_value, $prev_value = '' ) {
		return book_database()->book_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Delete Meta
	 *
	 * Remove metadata matching criteria from a book.
	 *
	 * @param string $meta_key   Meta name.
	 * @param mixed  $meta_value Meta value.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool False for failure, true for success.
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return book_database()->book_meta->delete_meta( $this->ID, $meta_key, $meta_value );
	}

	/**
	 * Sanitize Columns
	 *
	 * Sanitize the given data for update/create.
	 *
	 * @param array $data The data to sanitize.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return array The sanitized data, based off column defaults.
	 */
	private function sanitize_columns( $data ) {

		$columns        = book_database()->books->get_columns();
		$default_values = book_database()->books->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {

				case '%s' :
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;

				case '%d' :
					if ( ! is_numeric( $data[ $key ] ) || (int) $data[ $key ] !== absint( $data[ $key ] ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f' :
					// Convert what was given to a float
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default :
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;

			}

		}

		return $data;

	}

	/*
	 * Below: Callback functions.
	 */

	/**
	 * Get Cover ID
	 *
	 * Return the cover image attachment ID.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_cover_id() {
		return apply_filters( 'book-database/book/get/cover_id', $this->cover, $this->ID, $this );
	}

	/**
	 * Get Cover URL
	 *
	 * Return the URL for the cover image.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false
	 */
	public function get_cover_url( $size = 'full' ) {
		$cover_id  = $this->get_cover_id();
		$cover_url = false;

		if ( $cover_id ) {
			$cover_url = wp_get_attachment_image_url( absint( $cover_id ), apply_filters( 'book-database/book/get-cover-url-size', $size ) );
		}

		return apply_filters( 'book-database/book/get/cover_url', $cover_url, $cover_id, $this->ID, $this );
	}

	/**
	 * Get Cover
	 *
	 * Return full HTML markup for the cover image.
	 *
	 * @param string|array $size Desired image size.
	 * @param array        $args Arguments to use in wp_get_attachment_image()
	 *
	 * @uses   wp_get_attachment_image()
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false
	 */
	public function get_cover( $size = 'full', $args = array() ) {
		$cover_id = $this->get_cover_id();
		$image    = false;

		if ( $cover_id ) {
			$image = wp_get_attachment_image( absint( $cover_id ), $size, false, $args );
		}

		return apply_filters( 'book-database/book/get/cover', $image, $cover_id, $this->ID, $this );
	}

	/**
	 * Get Title
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_title() {
		return apply_filters( 'book-database/book/get/title', $this->title, $this->ID, $this );
	}

	/**
	 * Get Chosen Index Title
	 *
	 * Title used for sorting in the indexes.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_index_title() {
		return apply_filters( 'book-database/book/get/index_title', $this->index_title, $this->ID, $this );
	}

	/**
	 * Get Title Choices
	 *
	 * Array contains the current title, the generated alternative title (if applicable), and "other".
	 *
	 * @uses   bdb_generate_alternative_book_title()
	 *
	 * @param bool $include_custom Whether or not to include the "Custom" option.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_title_choices( $include_custom = false ) {

		$alt_title = bdb_generate_alternative_book_title( $this->get_title() );

		$choices = array(
			'original' => $this->get_title()
		);

		if ( $alt_title ) {
			$choices[ $alt_title ] = $alt_title;
		}

		if ( $include_custom ) {
			$choices['custom'] = esc_html__( 'Custom', 'book-database' );
		}

		return apply_filters( 'book-database/book/get/title-choices', $choices, $alt_title, $this->ID, $this );

	}

	/**
	 * Get Author
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|bool Array of author IDs and names or false if none.
	 */
	public function get_author() {

		if ( ! isset( $this->author ) ) {
			$this->author = bdb_get_book_author( $this->ID );
		}

		return apply_filters( 'book-database/book/get/author', $this->author, $this->ID, $this );
	}

	/**
	 * Get Author Names
	 *
	 * @param bool $implode Whether or not to implode the array into a string.
	 *
	 * @since 1.0.0
	 * @return array|string Array of author names or imploded string.
	 */
	public function get_author_names( $implode = true ) {

		$authors      = $this->get_author();
		$author_names = array();

		if ( is_array( $authors ) ) {
			foreach ( $authors as $author ) {
				$author_names[] = $author->name;
			}
		}

		if ( $implode ) {
			$author_names = implode( ', ', $author_names );
		}

		return apply_filters( 'book-database/book/get/author_names', $author_names, $authors, $this->ID, $this );

	}

	/**
	 * Get Series ID
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_series_id() {
		return apply_filters( 'book-database/book/get/series_id', $this->series_id, $this->ID, $this );
	}

	/**
	 * Get Series Position
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_series_position() {
		return apply_filters( 'book-database/book/get/series_position', $this->series_position, $this->ID, $this );
	}

	/**
	 * Get Series Name
	 *
	 * @todo   Cache? Or join with bdb_get_book_series_name() or idk..
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_series_name() {
		$series      = book_database()->series->get_series_by( 'ID', $this->get_series_id() );
		$series_name = ( $series && is_object( $series ) ) ? $series->name : false;

		return apply_filters( 'book-database/book/get/series_name', $series_name, $this->ID, $this );
	}

	/**
	 * Get Formatted Series
	 *
	 * Combines the series name and position.
	 *
	 * @param bool $linked Whether or not to include the link.
	 *
	 * @uses   bdb_get_formatted_series_name()
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false
	 */
	public function get_formatted_series( $linked = false ) {

		if ( ! $this->get_series_id() ) {
			return false;
		}

		global $wpdb;
		$book_table   = book_database()->books->table_name;
		$series_table = book_database()->series->table_name;
		$query        = $wpdb->prepare( "SELECT series.name, series.slug from $series_table as series INNER JOIN $book_table as book on series.ID = book.series_id WHERE book.ID = %d", absint( $this->ID ) );
		$series       = $wpdb->get_results( $query );

		if ( ! is_array( $series ) ) {
			return false;
		}

		$series = $series[0];

		$series_name = $series->name;
		$series_pos  = $this->get_series_position();

		if ( $series_pos ) {
			$formatted_name = sprintf( '%s #%s', $series_name, $series_pos );
		} else {
			$formatted_name = $series_name;
		}

		if ( $linked ) {
			$formatted_name = '<a href="' . esc_url( bdb_get_term_link( $series->slug, 'series' ) ) . '">' . esc_html( $formatted_name ) . '</a>';
		}

		return $formatted_name;

	}

	/**
	 * Get Publication Date
	 *
	 * Returned in MySQL date format.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_pub_date() {
		return apply_filters( 'book-database/book/get/pub_date', $this->pub_date, $this->ID, $this );
	}

	/**
	 * Get Formatted Publication Date
	 *
	 * @param string|bool $format Format to use for the date. Leave as false to use format specified in settings.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_formatted_pub_date( $format = false ) {
		if ( ! $format ) {
			$format = get_option( 'date_format' );
		}

		$raw_date = $this->get_pub_date();
		$date     = $raw_date ? mysql2date( $format, $raw_date ) : false;

		return apply_filters( 'book-database/book/get/formatted_pub_date', $date, $format, $raw_date, $this->ID, $this );
	}

	/**
	 * Get Pages
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|null
	 */
	public function get_pages() {
		return apply_filters( 'book-database/book/get/pages', $this->pages, $this->ID, $this );
	}

	/**
	 * Get Goodreads URL
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|null
	 */
	public function get_goodreads_url() {
		return apply_filters( 'book-database/book/get/goodreads_url', $this->goodreads_url, $this->ID, $this );
	}

	/**
	 * Get Purchase Link
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|null
	 */
	public function get_buy_link() {
		return apply_filters( 'book-database/book/get/buy_link', $this->buy_link, $this->ID, $this );
	}

	/**
	 * Get Synopsis
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_synopsis() {
		return apply_filters( 'book-database/book/get/synopsis', $this->synopsis, $this->ID, $this );
	}

	/**
	 * Set Rating
	 *
	 * This sets a faux rating so we can display it with the book information.
	 *
	 * @param string|int|null $rating
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function set_rating( $rating ) {
		$this->rating = $rating;
	}

	/**
	 * Get Rating
	 *
	 * Returns the faux rating.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|int|null
	 */
	public function get_rating() {
		return apply_filters( 'book-database/book/get/rating', $this->rating, $this->ID, $this );
	}

	/**
	 * Get the average rating of all reading logs associated with this book
	 *
	 * @access public
	 * @since  1.0
	 * @return float|int
	 */
	public function get_average_rating() {

		global $wpdb;
		$log_table  = book_database()->reading_list->table_name;
		$book_table = book_database()->books->table_name;
		$query      = $wpdb->prepare( "SELECT ROUND(AVG(IF(log.rating = 'dnf', 0, log.rating)), 2) FROM {$log_table} AS log LEFT JOIN {$book_table} AS book on log.book_id = book.ID WHERE book.ID = %d", $this->ID );
		$average    = $wpdb->get_var( $query );

		return apply_filters( 'book-database/book/get/average-rating', $average, $this->ID, $this );

	}

	/**
	 * Get Terms
	 *
	 * Returns all terms associated with the book, grouped by type.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|false
	 */
	public function get_terms() {

		if ( ! isset( $this->terms ) ) {
			$this->terms = bdb_get_all_book_terms( $this->ID );
		}

		return apply_filters( 'book-database/book/get/terms', $this->terms, $this->ID, $this );

	}

	/**
	 * Get Terms of Type
	 *
	 * Returns all terms of a certain type.
	 *
	 * @param string $type
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|false
	 */
	public function get_term_type( $type ) {

		if ( isset( $this->terms ) ) {
			$all_terms = $this->get_terms();
			$terms     = ( is_array( $all_terms ) && array_key_exists( $type, $all_terms ) ) ? $all_terms[ $type ] : false;
		} else {
			$terms = bdb_get_book_terms( $this->ID, $type );
		}

		return $terms;

	}

	/**
	 * Has Term
	 *
	 * Checks whether or not this book has a given term.
	 *
	 * @param string|int $name_or_id Term name or ID to check.
	 * @param string     $type       Term type (`author`, `genre`, etc.).
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function has_term( $name_or_id, $type ) {

		$terms = $this->get_term_type( $type );

		if ( ! is_array( $terms ) ) {
			return false;
		}

		$to_pluck = is_numeric( $name_or_id ) ? 'term_id' : 'name';
		$fields   = wp_list_pluck( $terms, $to_pluck );

		return in_array( $name_or_id, $fields );

	}

	/**
	 * Get Data
	 *
	 * Returns *all* data associated with a book.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_data() {

		$book = array(
			'ID'                  => $this->ID,
			'cover_id'            => $this->get_cover_id(),
			'cover_url'           => $this->get_cover_url( 'medium' ),
			'title'               => $this->get_title(),
			'index_title'         => $this->get_index_title(),
			'index_title_choices' => $this->get_title_choices(),
			'author'              => $this->get_author(),
			'author_comma'        => $this->get_author_names(),
			'series_id'           => $this->get_series_id(),
			'series_name'         => $this->get_series_name(),
			'series_position'     => $this->get_series_position(),
			'pub_date'            => $this->get_formatted_pub_date(),
			'pages'               => $this->get_pages(),
			'synopsis'            => $this->get_synopsis(),
			'goodreads_url'       => $this->get_goodreads_url()
		);

		return apply_filters( 'book-database/book/get/data', $book, $this->ID, $this );

	}

	/**
	 * Get Formatted Book Information
	 *
	 * Replaces placeholders with values.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_formatted_info() {
		$all_fields     = bdb_get_book_fields();
		$enabled_fields = bdb_get_option( 'book_layout', bdb_get_default_book_field_values() );
		$final_output   = '';

		foreach ( $enabled_fields as $key => $value ) {

			// Make sure the array key exists.
			if ( ! array_key_exists( $key, $all_fields ) ) {
				continue;
			}

			$template = $enabled_fields[ $key ]['label']; // Value entered by the user as a template.
			$find     = $all_fields[ $key ]['placeholder']; // Thing we need to look for and replace with a value.
			$value    = apply_filters( 'book-database/book/formatted-info/value/' . $key, false, $enabled_fields, $this->ID, $this );

			if ( ! $value ) {
				continue;
			}

			// Add line break if desired.
			if ( array_key_exists( 'linebreak', $enabled_fields[ $key ] ) && $enabled_fields[ $key ]['linebreak'] == 'on' ) {
				$template .= apply_filters( 'book-database/book/formatted-info/line-break', '<br>' );
			}

			// Replace the placeholder with the value.
			$final_value = str_replace( $find, $value, $template );

			// Add to output.
			$final_output .= apply_filters( 'book-database/book/formatted-info/final-value', $final_value, $key, $this );

		}

		// Maybe allow shortcodes.
		if ( apply_filters( 'book-database/book/formatted-info/allow-shortcodes', false ) ) {
			$final_output = do_shortcode( $final_output );
		}

		return apply_filters( 'book-database/book/formatted-info/output', $final_output, $this );
	}

}