<?php
/**
 * Admin Pages
 *
 * Creates admin pages and loads any required assets on these pages.
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
 * Admin Pages
 *
 * Top Level: Book Library
 * Submenus: Book Reviews, Settings
 *
 * @since 1.0.0
 * @return void
 */
function bdb_add_options_link() {
	// Book Library
	add_menu_page( sprintf( esc_html__( '%s Library', 'book-database' ), bdb_get_label_singular() ), sprintf( esc_html__( '%s Library', 'book-database' ), bdb_get_label_singular() ), 'edit_posts', 'bdb-books', 'bdb_books_page', 'dashicons-book' );

	// Book Reviews
	add_submenu_page( 'bdb-books', sprintf( esc_html__( '%s Reviews', 'book-database' ), bdb_get_label_singular() ), sprintf( esc_html__( '%s Reviews', 'book-database' ), bdb_get_label_singular() ), 'edit_posts', 'bdb-reviews', 'bdb_reviews_page' );

	// Analytics
	add_submenu_page( 'bdb-books', esc_html__( 'Review Analytics', 'book-database' ), esc_html__( 'Analytics', 'book-database' ), 'manage_options', 'bdb-analytics', 'bdb_analytics_page' );

	// Settings
	add_submenu_page( 'bdb-books', esc_html__( 'Book Database Settings', 'book-database' ), esc_html__( 'Settings', 'book-database' ), 'manage_options', 'bdb-settings', 'bdb_options_page' );
}

add_action( 'admin_menu', 'bdb_add_options_link', 10 );

/**
 * Is Admin Page
 *
 * Checks whether or not the current page is an UBB admin page.
 *
 * @since 1.0.0
 * @return bool
 */
function bdb_is_admin_page() {
	$screen      = get_current_screen();
	$is_bdb_page = false;

	$bdb_page_ids = array(
		'toplevel_page_bdb-books',
		'book-library_page_bdb-reviews',
		'book-library_page_bdb-settings'
	);

	if ( in_array( $screen->id, $bdb_page_ids ) ) {
		$is_bdb_page = true;
	}

	// Show where the reviews are included.
	if ( in_array( $screen->post_type, bdb_get_review_post_types() ) ) {
		$is_bdb_page = true;
	}

	//var_dump($screen);wp_die();

	// @todo

	return apply_filters( 'book-database/is-admin-page', $is_bdb_page, $screen );
}