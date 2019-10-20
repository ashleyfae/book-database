<?php
/**
 * Rewrite Rules
 *
 * Used for creating the dynamic taxonomy archives.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the URL to the reviews page
 *
 * @return string
 */
function get_reviews_page_url() {

	$page_id = bdb_get_option( 'reviews_page' );
	$url     = false;

	if ( $page_id ) {
		$url = get_permalink( absint( $page_id ) );
	}

	/**
	 * Filters the designated reviews page URL.
	 *
	 * @param string $url
	 */
	return apply_filters( 'book-database/reviews-page-url', $url );
}

/**
 * Get the reviews page slug
 *
 * @return string
 */
function get_reviews_page_slug() {

	$page_id = bdb_get_option( 'reviews_page' );
	$slug    = false;

	if ( $page_id ) {
		$page = get_post( absint( $page_id ) );
		$slug = $page->post_name;
	}

	/**
	 * Filters the review page slug.
	 *
	 * @param string $slug
	 */
	return apply_filters( 'book-database/reviews-page-slug', $slug );

}

/**
 * Get the reviews endpoint
 *
 * @return string
 */
function get_reviews_endpoint() {
	return get_reviews_page_slug();
}

/**
 * Add rewrite tags
 */
function add_rewrite_tags() {
	add_rewrite_tag( '%book_tax%', '([^&]+)' );
	add_rewrite_tag( '%book_term%', '([^&]+)' );
}

add_action( 'init', __NAMESPACE__ . '\add_rewrite_tags' );

/**
 * Add rewrite rules
 */
function add_rewrite_rules() {

	$page_id = bdb_get_option( 'reviews_page' );

	if ( empty( $page_id ) ) {
		return;
	}

	add_rewrite_rule( '^' . get_reviews_endpoint() . '/([^/]*)/([^/]*)/?', 'index.php?page_id=' . absint( $page_id ) . '&book_tax=$matches[1]&book_term=$matches[2]', 'top' );

}

add_action( 'init', __NAMESPACE__ . '\add_rewrite_rules' );