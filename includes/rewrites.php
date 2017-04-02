<?php
/**
 * Rewrite Rules
 *
 * Used for creating the dynamic taxonomy archives.
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
 * Get Reviews Page URL
 *
 * @since 1.0.0
 * @return string|false
 */
function bdb_get_reviews_page_url() {
	$page_id = bdb_get_option( 'reviews_page' );
	$url     = false;

	if ( $page_id ) {
		$url = get_permalink( absint( $page_id ) );
	}

	return apply_filters( 'book-database/reviews-page-url', $url );
}

/**
 * Get Reviews Page Slug
 *
 * @since 1.0.0
 * @return string|false
 */
function bdb_get_reviews_page_slug() {
	$page_id = bdb_get_option( 'reviews_page' );
	$slug    = false;

	if ( $page_id ) {
		$page = get_post( absint( $page_id ) );
		$slug = $page->post_name;
	}

	return apply_filters( 'book-database/reviews-page-slug', $slug );
}

/**
 * Get Reviews Endpoint
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_reviews_endpoint() {
	return apply_filters( 'book-database/rewrite/endpoint', bdb_get_reviews_page_slug() );
}

/**
 * Register Rewrite Tags
 *
 * @since 1.0.0
 * @return void
 */
function bdb_rewrite_tags() {
	add_rewrite_tag( '%book_tax%', '([^&]+)' );
	add_rewrite_tag( '%book_term%', '([^&]+)' );
}

add_action( 'init', 'bdb_rewrite_tags' );

/**
 * Create Rewrite Rules
 *
 * @since 1.0.0
 * @return void
 */
function bdb_rewrite_rules() {
	$page_id = bdb_get_option( 'reviews_page' );

	if ( ! $page_id ) {
		return;
	}

	add_rewrite_rule( '^' . bdb_get_reviews_endpoint() . '/([^/]*)/([^/]*)/?', 'index.php?page_id=' . absint( $page_id ) . '&book_tax=$matches[1]&book_term=$matches[2]', 'top' );
}

add_action( 'init', 'bdb_rewrite_rules' );