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
 * Ajax CB: Update or Create Book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_save_book() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_data = isset( $_POST['book'] ) ? $_POST['book'] : array();

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Error: You do not have permission to add books.', 'book-database' ) );
	}

	$new_book_id = bdb_insert_book( $book_data );

	wp_send_json_success( $new_book_id );
}

add_action( 'wp_ajax_bdb_save_book', 'bdb_ajax_save_book' );

/**
 * Ajax CB: Get Review by ID
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_review() {
	check_ajax_referer( 'book-database', 'nonce' );

	$review_id = isset( $_POST['review_id'] ) ? absint( $_POST['review_id'] ) : 0;

	if ( ! $review_id ) {
		wp_send_json_error( __( 'Error: Invalid review ID.', 'book-database' ) );
	}

	$review = bdb_get_review( $review_id );

	if ( is_object( $review ) ) {
		$data = array(
			'ID'     => $review->ID,
			'rating' => $review->get_rating()
		);
	} else {
		$data = array();
	}

	wp_send_json_success( $data );

	exit;
}

add_action( 'wp_ajax_bdb_get_review', 'bdb_ajax_get_review' );

/**
 * Ajax CB: Update or Create Review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_save_review() {
	check_ajax_referer( 'book-database', 'nonce' );

	$review_data = isset( $_POST['review'] ) ? $_POST['review'] : array();

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Error: You do not have permission to add reviews.', 'book-database' ) );
	}

	$new_review_id = bdb_insert_review( $review_data );
	$review        = new BDB_Review( $new_review_id );
	$book          = new BDB_Book( $review->book_id );
	$rating        = new BDB_Rating( $review->rating );

	$data = array(
		'ID'        => $new_review_id . ' <a href="' . esc_url( bdb_get_admin_page_edit_review( $new_review_id ) ) . '" target="_blank">' . __( '(Edit)', 'book-database' ) . '</a>',
		'book'      => esc_html( sprintf( _x( '%s by %s', 'book title by author name', 'book-database' ), $book->get_title(), $book->get_author_names() ) ),
		'rating'    => $review->rating ? $rating->format( 'text' ) : '&ndash',
		'shortcode' => '[book id="' . esc_attr( $book->ID ) . '"]',
		'remove'    => '<button class="button secondary bdb-remove-book-review">' . __( 'Remove', 'book-database' ) . '</button>'
	);

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_bdb_save_review', 'bdb_ajax_save_review' );

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

function bdb_ajax_search_book() {
	check_ajax_referer( 'book-database', 'nonce' );

	$search = isset( $_POST['search'] ) ? wp_strip_all_tags( $_POST['search'] ) : false;
	$field  = ( isset( $_POST['field'] ) && $_POST['field'] == 'author' ) ? 'author' : 'title';

	if ( ! $search ) {
		wp_send_json_error( __( 'Error: A search term is resquired.', 'book-database' ) );
	}

	$list_items = '';

	if ( 'author' == $field ) {
		$args = array(
			'author_name' => $search
		);
	} else {
		$args = array(
			'title' => $search
		);
	}

	$books = bdb_get_books( apply_filters( 'book-database/admin/books/search-book-args', $args, $search, $field ) );

	if ( ! is_array( $books ) ) {
		wp_send_json_error( __( 'No results found.', 'book-database' ) );
	}

	foreach ( $books as $book ) {
		$author_name = isset( $book->author_name ) ? $book->author_name : bdb_get_book_author_name( $book->ID );
		$list_items .= '<li><a href="#" data-id="' . esc_attr( $book->ID ) . '">' . sprintf( __( '%s by %s', 'book-database' ), $book->title, $author_name ) . '</a></li>';
	}

	wp_send_json_success( '<ul>' . $list_items . '</ul>' );

	exit;
}

add_action( 'wp_ajax_bdb_search_book', 'bdb_ajax_search_book' );