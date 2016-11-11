<?php
/**
 * Rewrite Rules
 *
 * Used for creating the dynamic taxonomy archives.
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
 * Get Reviews Endpoint
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_reviews_endpoint() {
	return apply_filters( 'book-database/rewrite/endpoint', 'reviews' );
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

add_action( 'init', 'bdb_rewrite_tags' ); // @todo add to install

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

add_action( 'init', 'bdb_rewrite_rules' ); // @todo add to install

/**
 * Rewrite Review Page Content
 *
 * If the tax/term query vars are present then rewrite the page to
 * show that specific archive.
 *
 * @param string $content
 *
 * @since 1.0.0
 * @return string
 */
function bdb_rewrite_review_page_content( $content ) {
	if ( get_the_ID() != bdb_get_option( 'reviews_page' ) ) {
		return $content;
	}

	global $wp_query;

	if ( ! array_key_exists( 'book_tax', $wp_query->query_vars ) || ! array_key_exists( 'book_term', $wp_query->query_vars ) ) {
		return $content;
	}

	$tax  = $wp_query->query_vars['book_tax'];
	$term = $wp_query->query_vars['book_term'];

	if ( empty( $tax ) || empty( $term ) ) {
		return $content;
	}

	return 'rewritten';
}

add_filter( 'the_content', 'bdb_rewrite_review_page_content' );