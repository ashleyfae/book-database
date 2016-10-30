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
 * Creates admin submenu pages under 'Books'.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_add_options_link() {
	// Book Review Page
	$book_review_page = add_menu_page( sprintf( esc_html__( '%s Reviews', 'book-database' ), bdb_get_label_singular() ), sprintf( esc_html__( '%s Reviews', 'book-database' ), bdb_get_label_singular() ), 'edit_posts', 'ubb-reviews', 'bdb_reviews_page', 'dashicons-book' );

	// Book Library
	$book_library_page = add_submenu_page( 'ubb-reviews', sprintf( esc_html__( '%s Library', 'book-database' ), bdb_get_label_singular() ), sprintf( esc_html__( '%s Library', 'book-database' ), bdb_get_label_singular() ), 'edit_posts', 'ubb-books', 'bdb_books_page' );

	// Main Menu Page.
	$bdb_page = add_menu_page( esc_html__( 'Book Database Settings', 'book-database' ), esc_html__( 'Book Database', 'book-database' ), 'activate_plugins', 'bookdb', 'bdb_options_page' );

	// Add submenus for each tab.
	foreach ( bdb_get_settings_tabs() as $key => $name ) {
		add_submenu_page( 'ultimatebb', esc_html( $name ), esc_html( $name ), 'activate_plugins', 'admin.php?page=ultimatebb&tab=' . $key );
	}

	// Now add other links.
	$bdb_tools_page      = add_submenu_page( 'ultimatebb', esc_html__( 'Ultimate Book Blogger Tools', 'book-database' ), esc_html__( 'Tools', 'book-database' ), 'activate_plugins', 'ubb-tools', 'bdb_tools_page' );
	$bdb_extensions_page = add_submenu_page( 'ultimatebb', esc_html__( 'Ultimate Book Blogger Extensions', 'book-database' ), esc_html__( 'Extensions', 'book-database' ), 'activate_plugins', 'ubb-extensions', 'bdb_extensions_page' );
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
		'toplevel_page_ubb-reviews',
		'book-reviews_page_ubb-books'
	);

	if ( in_array( $screen->id, $bdb_page_ids ) ) {
		$is_bdb_page = true;
	}

	//var_dump($screen);wp_die();

	// @todo

	return apply_filters( 'book-database/is-admin-page', $is_bdb_page, $screen );
}