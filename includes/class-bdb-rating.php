<?php

/**
 * Rating Class
 *
 * Used for formatting ratings.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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
	 * Maximum Rating Value
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $max;

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

		$this->max = bdb_get_max_rating();

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

		$rating          = is_numeric( $rating ) ? (float) $rating : $rating; // to remove trailing 0
		$allowed_ratings = bdb_get_available_ratings();
		if ( array_key_exists( (string) $rating, $allowed_ratings ) ) {
			$this->rating = $rating;

			return true;
		}

		return false;

	}

	/**
	 * Format Rating
	 *
	 * @param string $type Formatting type to use. Allowed:
	 *                     font_awesome - Font Awesome star icons.
	 *                     html_stars - HTML entities.
	 *                     text - Plain text with "Star(s)" appended.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|bool Formatted star rating or false on failure.
	 */
	public function format( $type = '' ) {

		$rating = false;

		if ( method_exists( $this, 'format_' . $type ) ) {

			$rating = call_user_func( array( $this, 'format_' . $type ) );

		}

		return $rating;

	}

	/**
	 * Format With Font Awesome
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function format_font_awesome() {

		if ( ! is_numeric( $this->rating ) ) {
			return $this->format_text();
		}

		return apply_filters( 'book-database/rating/format/font_awesome', $this->repeat( '<i class="fa fa-star"></i>', '<i class="fa fa-star-half-o"></i>', '<i class="fa fa-star-o"></i>' ), $this->rating, $this );

	}

	/**
	 * Format With HTML Stars
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function format_html_stars() {

		if ( ! is_numeric( $this->rating ) ) {
			return $this->format_text();
		}

		return apply_filters( 'book-database/rating/format/html_stars', $this->repeat( '&starf;', '&half;', '' ), $this->rating, $this );

	}

	/**
	 * Format as Text (with "Star(s)" appended)
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function format_text() {

		$text = $this->rating;

		if ( null === $this->rating ) {
			$text = '&ndash;';
		} else {
			$allowed = bdb_get_available_ratings();

			if ( array_key_exists( (string) $text, $allowed ) ) {
				$text = $allowed[ $text ];
			}
		}

		return apply_filters( 'book-database/rating/format/text', $text, $this->rating, $this );

	}

	/**
	 * Format as HTML Class
	 *
	 * For use in class attributes.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function format_html_class() {

		$allowed = bdb_get_available_ratings();
		$class   = '';

		switch ( $this->rating ) {

			case '5' :
				$class = 'five-stars';
				break;

			case '4.5' :
				$class = 'four-half-stars';
				break;

			case '4' :
				$class = 'four-stars';
				break;

			case '3.5' :
				$class = 'three-half-stars';
				break;

			case '3' :
				$class = 'three-stars';
				break;

			case '2.5' :
				$class = 'two-half-stars';
				break;

			case '2' :
				$class = 'two-stars';
				break;

			case '1.5' :
				$class = 'one-half-stars';
				break;

			case '1' :
				$class = 'one-star';
				break;

			case '0.5' :
				$class = 'half-star';
				break;

			case '0' :
				$class = 'zero-stars';
				break;

			case 'dnf' :
				$class = 'dnf';
				break;

		}

		return apply_filters( 'book-database/rating/format/html_class', $class, $allowed, $this->rating, $this );

	}

	/**
	 * Repeat Text/HTML for Number of Stars
	 *
	 * @param string $full_star  Text/HTML to repeat for number of full stars.
	 * @param string $half_star  Text/HTML to repeat for number of half stars.
	 * @param string $empty_star Text/HTML to repeat for number of empty stars.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function repeat( $full_star = '', $half_star = '', $empty_star = '' ) {

		$rating      = $this->rating;
		$full_stars  = floor( $rating );
		$half_stars  = ceil( $rating - $full_stars );
		$empty_stars = $this->max - $full_stars - $half_stars;

		$output = str_repeat( $full_star, $full_stars );
		$output .= str_repeat( $half_star, $half_stars );
		$output .= str_repeat( $empty_star, $empty_stars );

		return $output;

	}

}