<?php
/**
 * Review Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Review
 * @package Book_Database
 */
class Review extends Base_Object {

	protected $book_id = 0;

	protected $post_id = null;

	protected $user_id = 0;

	protected $url = '';

	protected $review = '';

	protected $date_written = '';

	protected $date_published = null;

	/**
	 * Get the ID of the book
	 *
	 * @return int
	 */
	public function get_book_id() {
		return absint( $this->book_id );
	}

	/**
	 * Get the ID of the corresponding post
	 *
	 * This is the post the review was published on. This will only be filled out if the
	 * review was published as a blog post on this site.
	 *
	 * @return int|null
	 */
	public function get_post_id() {
		return is_null( $this->post_id ) ? null : absint( $this->post_id );
	}

	/**
	 * Get the ID of the user who wrote the review
	 *
	 * @return int
	 */
	public function get_user_id() {
		return absint( $this->user_id );
	}

	/**
	 * Get the URL of the review
	 *
	 * This will only be filled out if the review is external.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get the review text
	 *
	 * @return string
	 */
	public function get_review() {
		return $this->review;
	}

	/**
	 * Get the date the review was written
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_date_written( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_written ) && $formatted ) ? format_date( $this->date_written, $format ) : $this->date_written;
	}

	/**
	 * Get the date the review was (or will be) published
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string|null
	 */
	public function get_date_published( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_published ) && $formatted ) ? format_date( $this->date_published, $format ) : $this->date_published;
	}

}