<?php
/**
 * Admin Assets
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Enqueue admin assets
 *
 * These assets are only loaded on BDB admin pages.
 *
 * @param string $hook
 */
function enqueue_admin_assets( $hook ) {

	if ( ! is_admin_page() ) {
		return;
	}

	// CSS
	wp_enqueue_style( 'book-database', BDB_URL . 'assets/css/admin-style.min.css', array(), time() );

	wp_enqueue_media();

	wp_enqueue_script( 'jquery-ui-datepicker' );

	// JS
	$deps = array( 'jquery', 'jquery-ui-sortable', 'suggest', 'wp-util' );
	wp_enqueue_script( 'book-database', BDB_URL . 'assets/js/build/admin.min.js', $deps, time(), true );

	$localized = array(
		'api_base'                   => esc_url_raw( rest_url() ),
		'api_nonce'                  => wp_create_nonce( 'wp_rest' ),
		'confirm_delete_author'         => __( 'Are you sure you want to delete this author?', 'book-database' ),
		'confirm_delete_book_term'      => __( 'Are you sure you want to delete this term?', 'book-database' ),
		'confirm_delete_book'           => __( 'Are you sure you want to delete this book?', 'book-database' ),
		'confirm_delete_edition'        => __( 'Are you sure you want to delete this edition?', 'book-database' ),
		'confirm_delete_reading_log'    => __( 'Are you sure you want to delete this reading log?', 'book-database' ),
		'confirm_delete_retailer'       => __( 'Are you sure you want to delete this retailer?', 'book-database' ),
		'confirm_delete_retailer_links' => __( 'Are you sure you want to delete this retailer? WARNING: This will also delete any purchase links that have been associated with this retailer. This cannot be undone.', 'book-database' ),
		'confirm_delete_review'         => __( 'Are you sure you want to delete this review?', 'book-database' ),
		'confirm_delete_series'         => __( 'Are you sure you want to delete this series?', 'book-database' ),
		'confirm_delete_taxonomy'       => __( 'Are you sure you want to delete this taxonomy?', 'book-database' ),
		'error_required_fields'         => esc_html__( 'Please fill out all the required fields.', 'book-database' ),
		'generic_error'                 => esc_html__( 'An unexpected error has occurred.', 'book-database' ),
		'is_admin'                   => is_admin(),
		'on_track_month'             => esc_html__( 'On track to read %d books this month.', 'book-database' ),
		'on_track_year'              => esc_html__( 'On track to read %d books this year.', 'book-database' ),
		'please_wait'                => esc_html__( 'Please wait...', 'book-database' ),
		'save'                       => esc_html__( 'Save', 'book-database' ),
		'stars'                      => esc_html__( 'Stars', 'book-database' )
	);

	wp_localize_script( 'book-database', 'bdbVars', $localized );

}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_assets' );

/**
 * Enqueue admin assets
 *
 * These assets are loaded on more "public" admin pages.
 *
 * @param string $hook
 */
function enqueue_admin_post_assets( $hook ) {

	$hooks = array(
		'edit.php',
		'post.php',
		'post-new.php',
		'index.php'
	);

	if ( ! in_array( $hook, $hooks ) ) {
		return;
	}

	// CSS
	wp_enqueue_style( 'book-database-global', BDB_URL . 'assets/css/admin-style-global.min.css', array(), time() );

	// JS
	$deps = array( 'jquery', 'wp-util' );
	wp_enqueue_script( 'book-database-global', BDB_URL . 'assets/js/build/admin-global.min.js', $deps, time(), true );

	$localized = array(
		'api_base'                          => esc_url_raw( rest_url() ),
		'api_nonce'                         => wp_create_nonce( 'wp_rest' ),
		'by'                                => esc_html__( 'by', 'book-database' ),
		'confirm_delete_review'             => __( 'Are you sure you want to delete this review? This will permanently delete the review record from the database.', 'book-database' ),
		'confirm_remove_review_association' => __( 'Are you sure you want to disassociate this review from this post? Note: the review itself will not be deleted, it will just no longer be linked with this post.', 'book-database' ),
		'confirm_dnf_book'               => __( 'Are you sure you\'d like to mark this book as DNF? This will set today as the date finished.', 'book-database' ),
		'confirm_finish_book'               => __( 'Are you sure you\'d like to mark this book as finished? This will change the progress to 100% and set today as the date finished.', 'book-database' ),
		'error_required_fields'             => esc_html__( 'Please fill out all the required fields.', 'book-database' ),
		'generic_error'                     => esc_html__( 'An unexpected error has occurred.', 'book-database' ),
		'no_books'                          => esc_html__( 'No books found.', 'book-database' ),
		'please_wait'                       => esc_html__( 'Please wait...', 'book-database' ),
		'prompt_percentage'                 => __( 'Enter your current percentage.', 'book-database' ),
		'stars'                             => esc_html__( 'Stars', 'book-database' ),
		'unknown'                           => esc_html__( 'unknown', 'book-database' )
	);

	wp_localize_script( 'book-database-global', 'bdbVars', $localized );

}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_post_assets' );