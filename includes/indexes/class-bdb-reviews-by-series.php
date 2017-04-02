<?php

/**
 * Reviews by Series
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
 * Class BDB_Reviews_by_Series
 *
 * @since 1.0.0
 */
class BDB_Reviews_by_Series extends BDB_Review_Index {

	/**
	 * Query Series
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function query_series() {

		$series = book_database()->series->get_series( array(
			'number'  => - 1,
			'orderby' => 'name',
			'order'   => 'DESC'
		) );

		return $series;

	}

	/**
	 * Query Reviews for a Given Series
	 *
	 * @param int $term_id ID of the term to look for.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function query( $series_id = false ) {

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT DISTINCT review.ID, review.post_id, review.url,
						log.rating as rating,
				        book.title, book.series_position,
				        series.ID as series_id, series.name as series_name,
				        author.term_id as author_id, GROUP_CONCAT(DISTINCT author.name SEPARATOR ', ') as author_name
				FROM {$this->tables['reviews']} as review
				LEFT JOIN {$this->tables['log']} as log ON review.ID = log.review_id
				INNER JOIN {$this->tables['books']} as book ON (review.book_id = book.ID AND book.series_id = %d)
				LEFT JOIN {$this->tables['series']} as series ON book.series_id = series.ID
				LEFT JOIN {$this->tables['relationships']} as r ON book.ID = r.book_id
				INNER JOIN {$this->tables['terms']} as author ON (r.term_id = author.term_id AND author.type = 'author')
				GROUP BY review.ID
				ORDER BY {$this->orderby}
				{$this->order}",
			absint( $series_id )
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
		$all_series = $this->query_series();

		$output            = '';
		$reviews_by_letter = array();

		$series_name_tag = ( 'yes' == $this->atts['letters'] ) ? 'h3' : 'h2';

		foreach ( $all_series as $series ) {
			$reviews = $this->query( $series->ID );

			// No reviews - bail.
			if ( ! count( $reviews ) ) {
				continue;
			}

			$series_reviews = array();
			$letter         = substr( $series->name, 0, 1 );

			foreach ( $reviews as $review ) {
				$series_reviews[] = $this->format_review( $review );
			}

			$reviews_by_letter[ strtolower( $letter ) ][ $series->name ] = array(
				'name'    => $series->name,
				'reviews' => $series_reviews
			);
		}

		foreach ( range( 'a', 'z' ) as $letter ) {

			if ( 'yes' == $this->atts['letters'] ) {
				$output .= '<h2 id="' . esc_attr( $letter ) . '">' . strtoupper( $letter ) . '</h2>';
			}

			// No reviews to add.
			if ( ! array_key_exists( $letter, $reviews_by_letter ) ) {
				continue;
			}

			$all_series = $reviews_by_letter[ $letter ];

			foreach ( $all_series as $series ) {
				// Show the series name. This is h2 or h3 depending on if letters are shown.
				$output .= '<' . $series_name_tag . ' id="' . esc_attr( sanitize_title( $series['name'] ) ) . '">' . esc_html( $series['name'] ) . '</' . $series_name_tag . '>';

				$output .= '<ul>' . implode( "\n", $series['reviews'] ) . '</ul>';
			}

		}

		return $output;
	}

}