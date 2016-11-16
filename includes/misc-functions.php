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
		'post'
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
	$url = admin_url( 'admin.php?page=bdb-books' );

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
 * @param int $book_id
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_edit_book( $book_id ) {
	$book_page      = bdb_get_admin_page_books();
	$edit_book_page = add_query_arg( array(
		'view' => 'edit',
		'ID'   => absint( $book_id )
	), $book_page );

	return apply_filters( 'book-database/admin-page-url/edit-book', $edit_book_page );
}

/**
 * Get Admin Page: Delete Book
 *
 * @param int $book_id
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_delete_book( $book_id ) {
	$book_page        = bdb_get_admin_page_books();
	$delete_book_page = add_query_arg( array(
		'bdb-action' => urlencode( 'book/delete' ),
		'ID'         => absint( $book_id ),
		'nonce'      => wp_create_nonce( 'bdb_delete_book' )
	), $book_page );

	return apply_filters( 'book-database/admin-page-url/delete-book', $delete_book_page );
}

/**
 * Get Admin Page: Reviews Table
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_reviews() {
	$url = admin_url( 'admin.php?page=bdb-reviews' );

	return apply_filters( 'book-database/admin-page-url/reviews', $url );
}

/**
 * Get Admin Page: Add Review
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_add_review( $book_id = 0 ) {
	$review = bdb_get_admin_page_reviews();

	$query_args = array(
		'view' => 'add'
	);

	if ( $book_id ) {
		$query_args['book_id'] = absint( $book_id );
	}

	$add_review_page = add_query_arg( $query_args, $review );

	return apply_filters( 'book-database/admin-page-url/add-review', $add_review_page );
}

/**
 * Get Admin Page: Edit Review
 *
 * @param int $review_id
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_edit_review( $review_id ) {
	$review_page      = bdb_get_admin_page_reviews();
	$edit_review_page = add_query_arg( array(
		'view' => 'edit',
		'ID'   => absint( $review_id )
	), $review_page );

	return apply_filters( 'book-database/admin-page-url/edit-review', $edit_review_page );
}

/**
 * Get Admin Page: Delete Review
 *
 * @param int $review_id
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_admin_page_delete_review( $review_id ) {
	$review_page        = bdb_get_admin_page_reviews();
	$delete_review_page = add_query_arg( array(
		'bdb-action' => urlencode( 'review/delete' ),
		'ID'         => absint( $review_id ),
		'nonce'      => wp_create_nonce( 'bdb_delete_review' )
	), $review_page );

	return apply_filters( 'book-database/admin-page-url/delete-review', $delete_review_page );
}

/**
 * Generate Unique Slug
 *
 * Checks to see if the given slug already exists. If so, numbers are appended
 * until the slug becomes available.
 *
 * @see   wp_unique_post_slug() - Based on this.
 *
 * @param string $slug Desired slug.
 * @param string $type Table type. Accepts any term type or `series`.
 *
 * @since 1.0.0
 * @return string Unique slug.
 */
function bdb_unique_slug( $slug, $type = 'author' ) {
	// Check if this slug already exists.
	if ( 'series' == $type ) {
		$terms = book_database()->series->get_series_by( 'slug', $slug );
	} else {
		$terms = book_database()->book_terms->get_terms( array(
			'type' => $type,
			'slug' => $slug
		) );
	}

	$new_slug = $slug;

	if ( $terms ) {
		$suffix = 2;

		do {
			$alt_slug = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";

			if ( 'series' == $type ) {
				$terms = book_database()->series->get_series_by( 'slug', $alt_slug );
			} else {
				$terms = book_database()->book_terms->get_terms( array(
					'type' => $type,
					'slug' => $alt_slug
				) );
			}

			$suffix ++;
		} while ( $terms );

		$new_slug = $alt_slug;
	}

	return apply_filters( 'book-database/unique-slug', $new_slug, $slug );
}

/**
 * Link Terms in Book Info
 *
 * Whether or not terms should link to the archive.
 *
 * Disable with this:
 *      `add_filter( 'book-database/link-terms', '__return_false' );`
 *
 * @since 1.0.0
 * @return bool
 */
function bdb_link_terms() {
	return apply_filters( 'book-database/link-terms', true );
}