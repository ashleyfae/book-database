<?php
/**
 * Misc Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns an array of post types that you can add reviews and
 * book information to.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_review_post_types() {
	$post_types = array(
		'post',
		'page'
	);

	return apply_filters( 'book-database/get-review-post-types', $post_types );
}

/**
 * Get Admin Page: Books Table
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_books() {
	$url = admin_url( 'admin.php?page=ubb-books' );

	return apply_filters( 'book-database/admin-page-url/books', $url );
}

/**
 * Get Admin Page: Add Book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_add_book() {
	$book_page     = bdb_get_admin_page_books();
	$add_book_page = add_query_arg( array(
		'view' => 'add'
	), $book_page );

	return apply_filters( 'book-database/admin-page-url/add-book', $add_book_page );
}

/**
 * Get Admin Page: Edit Book
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_edit_book( $book_id ) {
	$book_page     = bdb_get_admin_page_books();
	$edit_book_page = add_query_arg( array(
		'view' => 'edit',
		'ID'   => absint( $book_id )
	), $book_page );

	return apply_filters( 'book-database/admin-page-url/edit-book', $edit_book_page );
}