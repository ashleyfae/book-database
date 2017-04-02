<?php

/**
 * Reviews by Title
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
 * Class BDB_Reviews_by_Title
 *
 * @since 1.0.0
 */
class BDB_Reviews_by_Title extends BDB_Review_Index {

	/**
	 * Query for Reviews
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function query( $filter = false ) {

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT review.ID, review.post_id, review.url,
						log.rating as rating,
				        book.title, book.index_title, book.series_position,
				        series.ID as series_id, series.name as series_name,
				        author.term_id as author_id, GROUP_CONCAT(DISTINCT author.name SEPARATOR ', ') as author_name
				FROM {$this->tables['reviews']} as review
				LEFT JOIN {$this->tables['log']} as log ON review.ID = log.review_id
				INNER JOIN {$this->tables['books']} as book ON review.book_id = book.ID
				LEFT JOIN {$this->tables['series']} as series ON book.series_id = series.ID
				LEFT JOIN {$this->tables['relationships']} as r ON book.ID = r.book_id
				INNER JOIN {$this->tables['terms']} as author ON r.term_id = author.term_id
				WHERE author.type = %s
				GROUP BY review.ID
				ORDER BY {$this->orderby}
				{$this->order}",
			'author'
		);

		$reviews = $wpdb->get_results( $query );

		return $reviews;

	}

	/**
	 * Display
	 *
	 * Creates the overall markup for the index.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false
	 */
	public function display() {
		$reviews = $this->query();

		if ( ! $reviews ) {
			return false;
		}

		$output            = '';
		$reviews_by_letter = array();

		foreach ( $reviews as $review ) {

			$title        = $review->index_title ? $review->index_title : $review->title;
			$letter       = substr( $title, 0, 1 );
			$array_letter = is_numeric( $letter ) ? '#' : strtolower( $letter );

			$reviews_by_letter[ $array_letter ][] = $this->format_review( $review );

		}

		// Numbers
		if ( array_key_exists( '#', $reviews_by_letter ) ) {
			if ( 'yes' == $this->atts['letters'] ) {
				$output .= '<h2 id="numbers">#</h2>';
				$output .= '<ul>' . implode( "\n", $reviews_by_letter['#'] ) . '</ul>';
			}
		}

		// A-Z letters
		foreach ( range( 'a', 'z' ) as $letter ) {

			if ( 'yes' == $this->atts['letters'] ) {
				$output .= '<h2 id="' . esc_attr( $letter ) . '">' . strtoupper( $letter ) . '</h2>';
			}

			// No reviews to add.
			if ( ! array_key_exists( $letter, $reviews_by_letter ) ) {
				continue;
			}

			$output .= '<ul>' . implode( "\n", $reviews_by_letter[ $letter ] ) . '</ul>';

		}

		return $output;
	}

}