<?php
/**
 * Reading Log Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Reading_Log
 * @package Book_Database
 */
class Reading_Log extends Base_Object {

	protected $book_id = 0;

	protected $review_id = 0;

	protected $user_id = 0;

	protected $date_started = '';

	protected $date_finished = '';

	protected $percentage_complete = 0;

	protected $rating = null;

	/**
	 * Get the ID of the associated book
	 *
	 * @return int
	 */
	public function get_book_id() {
		return absint( $this->book_id );
	}

	/**
	 * Get the ID of the associated review
	 *
	 * @return int
	 */
	public function get_review_id() {
		return absint( $this->review_id );
	}

	/**
	 * Get the ID of the user who made this entry
	 *
	 * @return int
	 */
	public function get_user_id() {
		return absint( $this->user_id );
	}

	/**
	 * Get the date the user started reading
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_date_started( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_started ) && $formatted ) ? format_date( $this->date_started, $format ) : $this->date_started;
	}

	/**
	 * Get the date the user finished reading
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_date_finished( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_finished ) && $formatted ) ? format_date( $this->date_finished, $format ) : $this->date_finished;
	}

	/**
	 * Get the percentage complete
	 *
	 * Note: This is the `percentage_complete` value multiplied by 100.
	 *
	 * @return float
	 */
	public function get_percentage_complete() {
		$percentage = floatval( $this->percentage_complete );

		if ( $percentage >= 1 ) {
			$percentage = 1;
		} elseif ( $percentage <= 0 ) {
			$percentage = 0;
		}

		return round( $percentage * 100 );
	}

	/**
	 * Whether or not the book has been fully read
	 *
	 * @return bool
	 */
	public function is_complete() {
		return $this->get_percentage_complete() >= 100;
	}

	/**
	 * A book is "DNF" (didn't finish) if there's a finished date but the percentage is less than 100%.
	 *
	 * @return bool
	 */
	public function is_dnf() {
		return ! empty( $this->get_date_finished() ) && ! $this->is_complete();
	}

	/**
	 * Get the rating
	 *
	 * @return float|null
	 */
	public function get_rating() {
		return $this->rating;
	}

}