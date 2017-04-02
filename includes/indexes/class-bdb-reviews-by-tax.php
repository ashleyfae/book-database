<?php

/**
 * Reviews by Taxonomy
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
 * Class BDB_Reviews_by_Tax
 *
 * @since 1.0.0
 */
class BDB_Reviews_by_Tax extends BDB_Review_Index {

	/**
	 * Taxonomy type
	 *
	 * @var string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $taxonomy;

	/**
	 * BDB_Reviews_by_Tax constructor.
	 *
	 * @param array  $atts     Shortcode attributes.
	 * @param string $template Template for individual reviews.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct( array $atts, $template ) {
		$this->taxonomy = $atts['type'];

		parent::__construct( $atts, $template );
	}

	/**
	 * Query Terms
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function query_terms() {

		$authors = bdb_get_terms( array(
			'number' => - 1,
			'type'   => $this->taxonomy
		) );

		return $authors;

	}

	/**
	 * Query Reviews for a Given Term
	 *
	 * @param int $term_id ID of the term to look for.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function query( $term_id = false ) {

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
				INNER JOIN {$this->tables['terms']} as author ON (r.term_id = author.term_id AND author.type = 'author')
				WHERE book.ID IN (
					SELECT DISTINCT (book.ID) FROM {$this->tables['books']} book
					INNER JOIN {$this->tables['relationships']} r ON book.ID = r.book_id
					INNER JOIN {$this->tables['terms']} terms on r.term_id = terms.term_id AND terms.term_id = %d
				)
				GROUP BY review.ID
				ORDER BY {$this->orderby}
				{$this->order}",
			absint( $term_id )
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
		$terms = $this->query_terms();

		$output            = '';
		$reviews_by_letter = array();

		$term_name_tag = ( 'yes' == $this->atts['letters'] ) ? 'h3' : 'h2';

		foreach ( $terms as $term ) {
			$reviews = $this->query( $term->term_id );

			// No reviews - bail.
			if ( ! count( $reviews ) ) {
				continue;
			}

			$author_reviews = array();
			$letter         = substr( $term->name, 0, 1 );

			foreach ( $reviews as $review ) {
				$author_reviews[] = $this->format_review( $review );
			}

			$reviews_by_letter[ strtolower( $letter ) ][ $term->name ] = array(
				'name'    => $term->name,
				'reviews' => $author_reviews
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

			$terms = $reviews_by_letter[ $letter ];

			foreach ( $terms as $term ) {
				// Show the term name. This is h2 or h3 depending on if letters are shown.
				$output .= '<' . $term_name_tag . ' id="' . esc_attr( sanitize_title( $term['name'] ) ) . '">' . esc_html( $term['name'] ) . '</' . $term_name_tag . '>';

				$output .= '<ul>' . implode( "\n", $term['reviews'] ) . '</ul>';
			}

		}

		return $output;
	}

}