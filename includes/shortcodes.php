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
		'orderby' => 'title', // title, author, date, pub_date, series_position
		'order'   => 'ASC', // ASC, DESC
		'letters' => 'yes' // yes, no
	), $atts, 'book' );

	$output = '';

	switch ( $atts['type'] ) {

		case 'title' :
			$index  = new BDB_Reviews_by_Title( $atts, $content );
			$output = $index->display();
			break;

		case 'series' :
			$index  = new BDB_Reviews_by_Series( $atts, $content );
			$output = $index->display();
			break;

		default :
			$taxonomies = bdb_get_taxonomies( true );

			if ( ! array_key_exists( $atts['type'], $taxonomies ) ) {
				break;
			}

			$index  = new BDB_Reviews_by_Tax( $atts, $content );
			$output = $index->display();
			break;

	}

	return '<div class="review-index review-index-by-' . sanitize_html_class( $atts['type'] ) . '">' . $output . '</div>';
}

add_shortcode( 'review-index', 'bdb_review_index_shortcode' );