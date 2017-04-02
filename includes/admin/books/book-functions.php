<?php
/**
 * Admin Book Functions
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
 * Register Default Book Views
 *
 * @param array $views
 *
 * @since 1.0.0
 * @return array
 */
function bdb_register_default_book_views( $views ) {

	$default_views = array(
		'add'  => 'bdb_books_edit_view',
		'edit' => 'bdb_books_edit_view'
	);

	return array_merge( $views, $default_views );

}

add_filter( 'book-database/books/views', 'bdb_register_default_book_views' );

/**
 * Ajax CB: Get Alt Titles
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_alt_titles() {
	check_ajax_referer( 'book-database', 'nonce' );

	$title = wp_unslash( wp_strip_all_tags( $_POST['title'] ) );

	if ( ! $title ) {
		wp_send_json_error( __( 'Error: missing title.', 'book-database' ) );
	}

	$alt_title = bdb_generate_alternative_book_title( $title );

	if ( $alt_title ) {
		wp_send_json_success( $alt_title );
	}

	wp_send_json_error( __( 'No alt titles found.', 'book-dataabse' ) );

	exit;
}

add_action( 'wp_ajax_bdb_get_alt_titles', 'bdb_ajax_get_alt_titles' );