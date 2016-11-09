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