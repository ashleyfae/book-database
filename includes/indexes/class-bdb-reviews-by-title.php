<?php

/**
 * Reviews by Title
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
	public function query() {

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT review.ID, review.post_id, review.url, review.rating,
				        book.title, book.series_position,
				        series.ID as series_id, series.name as series_name,
				        author.term_id as author_id, author.name as author_name
				FROM {$this->tables['reviews']} as review
				INNER JOIN {$this->tables['books']} as book ON review.book_id = book.ID
				LEFT JOIN {$this->tables['series']} as series ON book.series_id = series.ID
				LEFT JOIN {$this->tables['relationships']} as r ON book.ID = r.book_id
				INNER JOIN {$this->tables['terms']} as author ON r.term_id = author.term_id
				WHERE author.type = %s
				ORDER BY {$this->orderby}
				{$this->order}",
			'author'
		);

		$reviews = $wpdb->get_results( $query );

		return $reviews;

	}

	public function display() {
		$reviews = $this->query();

		if ( ! $reviews ) {
			return false;
		}

		$output            = '';
		$reviews_by_letter = array();

		foreach ( $reviews as $review ) {

			$letter                                       = substr( $review->title, 0, 1 );
			$reviews_by_letter[ strtolower( $letter ) ][] = $this->format_review( $review );

		}

		foreach ( range( 'a', 'z' ) as $letter ) {

			$output .= '<h2 id="' . esc_attr( $letter ) . '">' . strtoupper( $letter ) . '</h2>';

			// No reviews to add.
			if ( ! array_key_exists( $letter, $reviews_by_letter ) ) {
				continue;
			}

			$output .= '<ul>' . implode( "\n", $reviews_by_letter[ $letter ] ) . '</ul>';

		}

		return $output;
	}

}