<?php
/**
 * Admin Book Functions
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
 * Save Book
 *
 * Triggers after saving a book via Book Reviews > Book Library.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_save_book() {

	$nonce = isset( $_POST['bdb_save_book_nonce'] ) ? $_POST['bdb_save_book_nonce'] : false;

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'bdb_save_book' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to edit books.', 'book-database' ) );
	}

	$book_id = absint( $_POST['book_id'] );

	$book_data = array(
		'ID' => $book_id
	);

	// Title
	if ( isset( $_POST['title'] ) ) {
		$book_data['title'] = $_POST['title'];
	}

	// @todo cover

	// Series Name
	if ( isset( $_POST['series_name'] ) ) {
		$book_data['series_name'] = $_POST['series_name'];
	}

	// Series ID
	if ( isset( $_POST['series_id'] ) ) {
		$book_data['series_id'] = $_POST['series_id'];
	}

	// Series Position
	if ( isset( $_POST['series_position'] ) ) {
		$book_data['series_position'] = $_POST['series_position'];
	}

	// Pub Date
	if ( isset( $_POST['pub_date'] ) ) {
		$book_data['pub_date'] = $_POST['pub_date'];
	}

	// Synopsis
	if ( isset( $_POST['synopsis'] ) ) {
		$book_data['synopsis'] = $_POST['synopsis'];
	}

	// @todo terms and meta

	$new_book_id = bdb_insert_book( $book_data );

	if ( ! $new_book_id || is_wp_error( $new_book_id ) ) {
		wp_die( __( 'An error occurred while inserting the book information.', 'book-database' ) );
	}

	$edit_url = add_query_arg( array(
		'update-success',
		'true'
	), bdb_get_admin_page_edit_book( absint( $new_book_id ) ) );

	wp_safe_redirect( $edit_url );

	exit;

}

add_action( 'book-database/book/save', 'bdb_save_book' );