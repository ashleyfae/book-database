<?php
/**
 * Front-end Assets
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Load assets
 */
function load_assets() {

	global $post;

	$review_page_id = bdb_get_option( 'reviews_page' );

	/*
	 * Bail if:
	 *
	 * - There is no reviews page set; or
	 * - The the current page ID doesn't match the selected reviews page;
	 * AND
	 * - The global `$post` variable isn't set; or
	 * - The current post isn't using the `[book-grid]` shortcode.
	 */
	if ( ( ! $review_page_id || get_the_ID() != $review_page_id ) && ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'book-grid' ) ) ) {
		return;
	}

	$css_dir = BDB_URL . 'assets/css/';

	wp_enqueue_style( 'book-database', $css_dir . 'front-end.min.css', array(), BDB_VERSION );

}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_assets' );