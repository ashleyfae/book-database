<?php

/**
 * Rating Class
 *
 * Used for formatting ratings.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_Rating
 *
 * @since 1.0.0
 */
class BDB_Rating {

	/**
	 * Rating
	 *
	 * Raw rating value from the database.
	 *
	 * @var string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $rating;

	/**
	 * BDB_Rating constructor.
	 *
	 * @param bool|string|int|float $rating Rating value.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct( $rating = false ) {

		if ( $rating ) {
			$this->set_rating( $rating );
		}

	}

	/**
	 * Set Rating
	 *
	 * @param string|int|float $rating Rating value to set.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool Whether or not set up was successful.
	 */
	public function set_rating( $rating ) {

		$allowed_ratings = bdb_get_available_ratings();

		if ( array_key_exists( $rating, $allowed_ratings ) ) {
			$this->rating = $rating;

			return true;
		}

		return false;

	}

	public function format( $type = '' ) {

		$rating = false;

		if ( ! is_numeric( $this->rating ) ) {

			$rating = $this->rating;

		} elseif ( method_exists( $this, 'format_' . $type ) ) {

			$rating = call_user_func( array( $this, 'format_' . $type ) );

		}

		return $rating;

	}

	public function format_html_stars() {

		if ( ! is_numeric( $this->rating ) ) {
			return false;
		}

		$rating      = $this->rating;
		$full_stars  = floor( $rating );
		$half_stars  = ceil( $rating - $full_stars );
		$empty_stars = 5 - $full_stars - $half_stars;

		$output = str_repeat( '&starf;', $full_stars );
		$output .= str_repeat( '&half;', $half_stars );
		$output .= str_repeat( '&star;', $empty_stars );

		return $output;

	}

}