<?php
/**
 * Ajax Callbacks Used in the Modal
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
 * Ajax CB: Get Book by ID
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_book() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_id = isset( $_POST['book_id'] ) ? absint( $_POST['book_id'] ) : 0;

	if ( ! $book_id ) {
		wp_send_json_error( __( 'Error: Invalid book ID.', 'book-database' ) );
	}

	$book = bdb_get_book( $book_id );
	$data = $book ? $book->get_data() : array();

	wp_send_json_success( $data );

	exit;
}

add_action( 'wp_ajax_bdb_get_book', 'bdb_ajax_get_book' );

/**
 * Ajax CB: Get Thumbnail URL from ID
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_thumbnail() {
	check_ajax_referer( 'book-database', 'nonce' );

	$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;
	$thumb    = wp_get_attachment_image_url( $image_id, 'medium' );

	wp_send_json_success( $thumb );

	exit;
}

add_action( 'wp_ajax_bdb_get_thumbnail', 'bdb_ajax_get_thumbnail' );