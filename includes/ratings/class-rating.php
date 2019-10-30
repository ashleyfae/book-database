<?php
/**
 * Rating Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Rating
 * @package Book_Database
 */
class Rating {

	/**
	 * Raw rating value from the database
	 *
	 * @var null|float|int
	 */
	protected $rating;

	/**
	 * Highest possible rating
	 *
	 * @var float|int
	 */
	protected $max;

	/**
	 * Available ratings
	 *
	 * @var array
	 */
	protected $available_ratings;

	/**
	 * Rating constructor.
	 *
	 * @param null|float|int $rating
	 */
	public function __construct( $rating = null ) {
		$this->set_rating( $rating );
		$this->available_ratings = get_available_ratings();
		$this->max               = max( array_keys( $this->available_ratings ) );
	}

	/**
	 * Set the rating
	 *
	 * @param null|float|int $rating Rating value to set.
	 */
	public function set_rating( $rating ) {
		if ( is_null( $rating ) ) {
			$this->rating = null;
		} elseif ( is_numeric( $rating ) ) {
			$this->rating = $rating + 0;
		} else {
			$this->rating = $rating;
		}
	}

	/**
	 * Get the raw rating
	 *
	 * @return float|int|null
	 */
	public function get_rating() {
		return $this->rating;
	}

	/**
	 * Get the highest possible rating
	 *
	 * @return float|int
	 */
	public function get_max_rating() {
		return $this->max;
	}

	/**
	 * Round the rating to the nearest half
	 *
	 * @return float|int|null
	 */
	public function round_rating() {

		$rating = $this->rating;

		if ( is_null( $rating ) || ! is_numeric( $rating ) ) {
			return null;
		}

		if ( ! array_key_exists( (string) $rating, $this->available_ratings ) ) {
			$rating = round( $this->rating * 2 ) / 2;
		}

		return $rating;

	}

	/**
	 * Format the rating
	 *
	 * @param string $format
	 *
	 * @return float|int|null
	 */
	public function format( $format = 'text' ) {

		$rating = $this->round_rating();

		if ( is_null( $format ) || ! is_numeric( $rating ) ) {
			return $rating;
		} elseif ( method_exists( $this, 'format_' . $format ) ) {
			return call_user_func( array( $this, 'format_' . $format ) );
		} else {
			return $rating;
		}

	}

	/**
	 * Format as text (with "Star(s)" appended)
	 *
	 * @return string
	 */
	public function format_text() {

		if ( null === $this->rating ) {
			$text = '&ndash;';
		} elseif ( ! is_numeric( $this->rating ) ) {
			return $this->rating;
		} else {
			$text = $this->rating * 1;

			if ( array_key_exists( (string) $text, $this->available_ratings ) ) {
				$text = $this->available_ratings[ (string) $text ];
			} else {
				$text = sprintf( _n( '%s Star', '%s Stars', $text, 'book-database' ), $text );
			}
		}

		/**
		 * Filters the text display.
		 *
		 * @param string         $text
		 * @param null|float|int $rating
		 * @param Rating         $this
		 */
		return apply_filters( 'book-database/rating/format/text', $text, $this->rating, $this );

	}

	/**
	 * Format with Font Awesome stars
	 *
	 * @return string
	 */
	public function format_font_awesome() {

		if ( ! is_numeric( $this->rating ) ) {
			return $this->format_text();
		}

		$font_awesome_rating = $this->repeat( '<i class="fas fa-star"></i>', '<i class="fas fa-star-half-alt"></i>', '<i class="far fa-star"></i>' );

		/**
		 * Filters the Font Awesome rating display.
		 *
		 * @param string         $font_awesome_rating
		 * @param null|float|int $rating
		 * @param Rating         $this
		 */
		return apply_filters( 'book-database/rating/format/font_awesome', $font_awesome_rating, $this->rating, $this );

	}

	/**
	 * Format with HTML stars
	 *
	 * @return string
	 */
	public function format_html_stars() {

		if ( ! is_numeric( $this->rating ) ) {
			return $this->format_text();
		}

		$html_stars = $this->repeat( '&starf;', '&half;', '' );

		/**
		 * Filters the HTML stars display.
		 *
		 * @param string         $html_stars
		 * @param null|float|int $rating
		 * @param Rating         $this
		 */
		return apply_filters( 'book-database/rating/format/html_stars', $html_stars, $this->rating, $this );

	}

	/**
	 * Format as HTML class name
	 *
	 * @return string
	 */
	public function format_html_class() {

		$class  = '';
		$rating = $this->round_rating();

		switch ( $rating ) {
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
		}

		/**
		 * Filters the HTML class display
		 *
		 * @param string         $class
		 * @param null|float|int $rating
		 * @param Rating         $this
		 */
		return apply_filters( 'book-database/rating/format/html_class', $class, $this->rating, $this );

	}

	/**
	 * Repeat text/HTML for the number of stars
	 *
	 * @param string $full_star  Text/HTML to use for full stars.
	 * @param string $half_star  Text/HTML to use for half stars.
	 * @param string $empty_star Text/HTML to use for empty stars.
	 *
	 * @return string
	 */
	public function repeat( $full_star, $half_star = '', $empty_star = '' ) {

		$rating      = $this->round_rating();
		$full_stars  = floor( $rating );
		$half_stars  = ceil( $rating - $full_stars );
		$empty_stars = $this->max - $full_stars - $half_stars;

		$output = str_repeat( $full_star, $full_stars );
		$output .= str_repeat( $half_star, $half_stars );
		$output .= str_repeat( $empty_star, $empty_stars );

		return $output;

	}

}