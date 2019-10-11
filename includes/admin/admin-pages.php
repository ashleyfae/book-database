<?php
/**
 * Admin Pages
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Register admin pages
 */
function register_admin_pages() {

	global $bdb_admin_pages;

	// Book Library
	$bdb_admin_pages['books'] = add_menu_page( __( 'Book Library', 'book-database' ), __( 'Book Library', 'book-database' ), 'edit_posts', 'bdb-books', __NAMESPACE__ . '\render_books_page', 'dashicons-book' );

	// Series
	$bdb_admin_pages['series'] = add_submenu_page( 'bdb-books', __( 'Book Series', 'book-database' ), __( 'Book Series', 'book-database' ), 'edit_posts', 'bdb-series', __NAMESPACE__ . '\render_book_series_page' );

	// Reviews
	$bdb_admin_pages['reviews'] = add_submenu_page( 'bdb-books', __( 'Book Reviews', 'book-database' ), __( 'Book Reviews', 'book-database' ), 'edit_posts', 'bdb-reviews', __NAMESPACE__ . '\render_book_reviews_page' );

	// Book Terms
	$bdb_admin_pages['reviews'] = add_submenu_page( 'bdb-books', __( 'Book Terms', 'book-database' ), __( 'Book Terms', 'book-database' ), 'edit_posts', 'bdb-terms', __NAMESPACE__ . '\render_book_terms_page' );

	// Analytics
	$bdb_admin_pages['reviews'] = add_submenu_page( 'bdb-books', __( 'Analytics', 'book-database' ), __( 'Analytics', 'book-database' ), 'manage_options', 'bdb-analytics', __NAMESPACE__ . '\render_analytics_page' );

	// Settings
	$bdb_admin_pages['settings'] = add_submenu_page( 'bdb-books', __( 'Settings', 'book-database' ), __( 'Settings', 'book-database' ), 'manage_options', 'bdb-settings', __NAMESPACE__ . '\render_settings_page' );

}

add_action( 'admin_menu', __NAMESPACE__ . '\register_admin_pages' );

/**
 * Whether or not the current page is a Book Database admin page.
 *
 * @return bool
 */
function is_admin_page() {

	global $bdb_admin_pages;

	$screen = get_current_screen();

	return in_array( $screen->id, $bdb_admin_pages );

}

/**
 * Add a `bdb-admin-page` class to all BDB admin pages.
 *
 * @param string $classes
 *
 * @return string
 */
function admin_body_class( $classes ) {

	if ( is_admin_page() ) {
		$classes .= ' bdb-admin-page ';
	}

	return $classes;

}

add_filter( 'admin_body_class', __NAMESPACE__ . '\admin_body_class' );