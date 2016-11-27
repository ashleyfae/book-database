<?php

/**
 * Review Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_Review {

	/**
	 * The review ID
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $ID = 0;

	/**
	 * ID of the associated book
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $book_id;

	/**
	 * ID of the post this review is associated with
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $post_id;

	/**
	 * URL to the book review (if not associated with
	 * a blog post)
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public $url;

	/**
	 * ID of the user who created this review
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $user_id;

	/**
	 * Rating
	 *
	 * @var int|float|string
	 * @access public
	 * @since  1.0.0
	 */
	public $rating;

	/**
	 * Date the review was written
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public $date_written;

	/**
	 * Review's publication date
	 *
	 * @var string
	 * @access public
	 * @since  1.0.0
	 */
	public $date_published;

	/**
	 * The database abstraction
	 *
	 * @var BDB_DB_Reviews
	 * @access protected
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * BDB_Review constructor.
	 *
	 * @param int|object $id Review ID or object from database.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool False if set up failed.
	 */
	public function __construct( $id ) {

		$this->db = new BDB_DB_Reviews();

		$review = is_object( $id ) ? $id : book_database()->reviews->get_review_by( 'ID', $id );

		if ( empty( $review ) || ! is_object( $review ) ) {
			return false;
		}

		return $this->setup_review( $review );

	}

	/**
	 * Setup Review
	 *
	 * Given the review data, let's set up the variables.
	 *
	 * @param object $review Review object from the database.
	 *
	 * @access private
	 * @since  1.0.0
	 * @return bool Whether or not the set up was successful.
	 */
	private function setup_review( $review ) {

		if ( ! is_object( $review ) ) {
			return false;
		}

		foreach ( $review as $key => $value ) {

			$this->$key = $value;

		}

		// We absolutely need an ID and book ID. Otherwise nothing works.
		if ( ! empty( $this->ID ) && ! empty( $this->book_id ) ) {
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

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'ubb-review-invalid-property', sprintf( __( 'Can\'t get property %s', 'book-database' ), $key ) );
		}

	}

	/**
	 * Create a Review
	 *
	 * @param array $data
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false Review ID if success, or false if failure.
	 */
	public function create( $data = array() ) {

		if ( $this->ID != 0 || empty( $data ) ) {
			return false;
		}

		$current_user = wp_get_current_user();

		$defaults = array(
			'user_id' => $current_user->ID
		);

		$args = wp_parse_args( $data, $defaults );
		$args = $this->sanitize_columns( $args );

		if ( empty( $args['book_id'] ) || ! is_numeric( $args['book_id'] ) ) {
			return false;
		}

		/**
		 * Firest before a review is created.
		 *
		 * @param array $args Contains review information.
		 */
		do_action( 'book-database/review/pre-create', $args );

		$created = false;

		// The DB class 'add' implies an update if the customer asked to be created already exists.
		$review_id = $this->db->add( $data );

		if ( $review_id ) {

			// We've successfully added/updated the customer, reset the class vars with the new data.
			$review = $this->db->get_review( $review_id );

			$this->setup_review( $review );

			$created = $this->ID;

		}

		/**
		 * Fires after a review is created.
		 *
		 * @param int   $created If created successfully, the review ID. Defaults to false.
		 * @param array $args    Contains review information.
		 */
		do_action( 'book-database/review/post-create', $created, $args );

		return $created;

	}

	/**
	 * Update a Review
	 *
	 * @param array $data Array of data attributes for a review (checked via whitelist).
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
		 * Firest before a review is updated.
		 *
		 * @param int   $this ->id ID of the review being updated.
		 * @param array $data Contains new review information.
		 */
		do_action( 'book-database/review/pre-update', $this->ID, $data );

		$updated = false;

		if ( $this->db->update( $this->ID, $data ) ) {

			$review = $this->db->get_review( $this->ID );
			$this->setup_review( $review );

			$updated = true;

		}

		/**
		 * Firest after a review is updated.
		 *
		 * @param bool  $updated Whether or not the update was successful.
		 * @param int   $this    ->id ID of the review being updated.
		 * @param array $data    Contains new review information.
		 */
		do_action( 'book-database/review/post-update', $updated, $this->ID, $data );

		return $updated;

	}

	/**
	 * Get Meta
	 *
	 * Retrieve review meta field for a review.
	 *
	 * @param string $meta_key The meta key to retrieve.
	 * @param bool   $single   Whether or not to return a single value.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return book_database()->review_meta->get_meta( $this->ID, $meta_key, $single );
	}

	/**
	 * Add Meta
	 *
	 * Add meta data field to a review.
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
		return book_database()->review_meta->add_meta( $this->ID, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update Meta
	 *
	 * Update review meta field based on review ID.
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
		return book_database()->review_meta->update_meta( $this->ID, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Delete Meta
	 *
	 * Remove metadata matching criteria from a review.
	 *
	 * @param string $meta_key   Meta name.
	 * @param mixed  $meta_value Meta value.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool False for failure, true for success.
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return book_database()->review_meta->delete_meta( $this->ID, $meta_key, $meta_value );
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

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

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

	/**
	 * Get ID
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function get_ID() {
		return apply_filters( 'book-database/review/get/ID', $this->ID, $this->ID, $this );
	}

	/**
	 * Get Book ID
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|BDB_Book|false Book ID, object, or false if none.
	 */
	public function get_book_id( $format = 'ID' ) {
		if ( 'ID' == $format ) {
			$book = $this->book_id ? $this->book_id : false;
		} else {
			$book = $this->book_id ? new BDB_Book( $this->book_id ) : false;
		}

		return apply_filters( 'book-database/review/get/book_id', $book, $this->ID, $this );
	}

	/**
	 * Get Post ID
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false Post ID, or false if none.
	 */
	public function get_post_id() {
		$post_id = $this->post_id ? $this->post_id : false;

		return apply_filters( 'book-database/review/get/post_id', $post_id, $this->ID, $this );
	}

	/**
	 * Get User ID
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false User ID, or false if none.
	 */
	public function get_user_id() {
		$user_id = $this->user_id ? $this->user_id : false;

		return apply_filters( 'book-database/review/get/user_id', $user_id, $this->ID, $this );
	}

	/**
	 * Get Reviewer
	 *
	 * Returns the WP_User object for the reviewer.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return WP_User|false
	 */
	public function get_reviewer() {

		$user_id = $this->get_user_id();
		$user    = false;

		if ( $user_id ) {
			$user = get_user_by( 'ID', $user_id );
		}

		return apply_filters( 'book-database/review/get/reviewer', $user, $this->ID, $this );

	}

	/**
	 * Get Reviewer Name
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false
	 */
	public function get_reviewer_name() {

		$user = $this->get_reviewer();
		$name = false;

		if ( $user ) {
			$name = $user->display_name;
		}

		return apply_filters( 'book-database/review/get/reviewer_name', $name, $this->ID, $this );

	}

	/**
	 * Get External URL
	 *
	 * Returns the URL to the external review if provided, or false if none is set.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false URL to review or false if none.
	 */
	public function get_external_url() {

		$url = false;

		if ( $this->url ) {
			$url = $this->url;
		}

		return apply_filters( 'book-database/review/get/url', $url, $this->ID, $this );

	}

	/**
	 * Get Permalink
	 *
	 * Returns the URL to the external review if provided, otherwise the URL to
	 * the post where the review is located (if provided). Return false if all
	 * else fails.
	 *
	 * @param bool $use_id      Whether or not to build the post URL with the ID rather
	 *                          than pretty permalinks. By setting to `true`, fewer queries
	 *                          are performed.
	 * @param bool $id_appended Whether or not to append #book-{#} to the URL to jump to
	 *                          the book information.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false URL to review or false if none.
	 */
	public function get_permalink( $use_id = true, $id_appended = true ) {

		$url = false;

		if ( $this->get_external_url() ) {
			$url = $this->get_external_url();
		} elseif ( $this->post_id && $use_id ) {
			$url = add_query_arg( array( 'p' => absint( $this->post_id ) ), home_url() );
		} elseif ( $this->post_id ) {
			$url = get_permalink( absint( $this->post_id ) );
		}

		if ( apply_filters( 'book-database/review/permalink/append-book-id', $id_appended, $this ) && ! empty( $url ) && ! $this->is_external() ) {
			$url .= '#book-' . absint( $this->book_id );
		}

		return apply_filters( 'book-database/review/get/permalink', $url, $use_id, $this->ID, $this );

	}

	/**
	 * Is External
	 *
	 * If a `url` field is provided then the review is considered external.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function is_external() {
		$external = (bool) $this->url;

		return apply_filters( 'book-database/review/is-external', $external, $this->ID, $this );
	}

	/**
	 * Get Rating
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_rating() {
		if ( ! isset( $this->rating ) ) {
			$log = bdb_get_review_reading_entry( $this->ID );

			if ( $log ) {
				$this->rating = $log->rating;
			}
		}

		return apply_filters( 'book-database/review/get/rating', $this->rating, $this->ID, $this );
	}

	/**
	 * Get Date Written
	 *
	 * Returned in MySQL date format.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_date() {
		return apply_filters( 'book-database/review/get/date_written', $this->date_written, $this->ID, $this );
	}

	/**
	 * Get Formatted Date Added
	 *
	 * @param string|bool $format Format to use for the date. Leave as false to use format specified in settings.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_formatted_date( $format = false ) {

		$raw_date = $this->get_date();
		$date     = $this->format_date( $raw_date, $format );

		return apply_filters( 'book-database/review/get/formatted_date', $date, $format, $raw_date, $this->ID, $this );

	}

	/**
	 * Get Date Published
	 *
	 * Returned in MySQL date format.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function get_date_published() {
		return apply_filters( 'book-database/review/get/date_published', $this->date_published, $this->ID, $this );
	}

	/**
	 * Format Date
	 *
	 * @param string      $raw_date MySQL date format.
	 * @param string|bool $format   Format to use for the date. Leave as false to use format specified in settings.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return mixed|void
	 */
	public function format_date( $raw_date, $format = false ) {

		if ( false == $format ) {
			$format = get_option( 'date_format' );
		}

		$date = $raw_date ? mysql2date( $format, $raw_date ) : false;

		return apply_filters( 'book-database/review/format_date', $date, $format, $raw_date, $this->ID, $this );

	}

	/**
	 * Is Review Published
	 *
	 * Returns `true` if the review is external or if the review published date
	 * is in the past.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function is_review_published() {

		if ( $this->is_external() ) {
			return true;
		}

		return ( strtotime( $this->get_date_published() ) <= time() );

	}

}