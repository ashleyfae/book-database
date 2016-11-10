<?php
/**
 * Shortcodes
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
 * Book Info Shortcode
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'id' => 0
	), $atts, 'book' );

	if ( ! $atts['id'] || ! is_numeric( $atts['id'] ) ) {
		return sprintf( __( 'Invalid book: %s', 'book-database' ), $atts['id'] );
	}

	$book      = new BDB_Book( absint( $atts['id'] ) );
	$book_info = $book->get_formatted_info();

	return apply_filters( 'book-database/shortcodes/book/output', $book_info, $book, $atts, $content );
}

add_shortcode( 'book', 'bdb_book_shortcode' );

/**
 * Review Index
 *
 * @param array  $atts
 * @param string $content
 *
 * @since 1.0.0
 * @return string
 */
function bdb_review_index_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'type'    => 'title', // title, author, series, publisher, genre
		'orderby' => 'title', // title, author
		'order'   => 'ASC' // ASC, DESC
	), $atts, 'book' );

	global $wpdb;

	$review_table       = book_database()->reviews->table_name;
	$book_table         = book_database()->books->table_name;
	$series_table       = book_database()->series->table_name;
	$term_table         = book_database()->book_terms->table_name;
	$relationship_table = book_database()->book_term_relationships->table_name;

	$allowed_orderby = array(
		'title'  => 'book.title',
		'author' => 'author.name'
	);

	$orderby = array_key_exists( $atts['orderby'], $allowed_orderby ) ? $allowed_orderby[ $atts['orderby'] ] : $allowed_orderby['title'];
	$order   = strtoupper( $atts['order'] ) == 'ASC' ? 'ASC' : 'DESC';

	switch ( $atts['type'] ) {

		case 'title' :
			$query = $wpdb->prepare(
				"SELECT DISTINCT review.ID, review.post_id, review.url, review.rating,
				        book.title, book.series_position,
				        series.ID as series_id, series.name as series_name,
				        author.term_id as author_id, author.name as author_name
				FROM {$review_table} as review
				INNER JOIN {$book_table} as book ON review.book_id = book.ID
				LEFT JOIN {$series_table} as series ON book.series_id = series.ID
				LEFT JOIN {$relationship_table} as r ON book.ID = r.book_id
				INNER JOIN {$term_table} as author ON r.term_id = author.term_id
				WHERE author.type = %s
				ORDER BY {$orderby}
				{$order}",
				'author'
			);
			break;

		case 'author' :
			// @todo
			break;

		case 'series' :
			// @todo
			break;

	}

	$result = $wpdb->get_results( $query );

	var_dump( $result );
}

add_shortcode( 'review-index', 'bdb_review_index_shortcode' );