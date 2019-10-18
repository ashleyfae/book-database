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
		'confirm_delete_author'      => __( 'Are you sure you want to delete this author?', 'book-database' ),
		'confirm_delete_book_term'   => __( 'Are you sure you want to delete this term?', 'book-database' ),
		'confirm_delete_book'        => __( 'Are you sure you want to delete this book?', 'book-database' ),
		'confirm_delete_edition'     => __( 'Are you sure you want to delete this edition?', 'book-database' ),
		'confirm_delete_reading_log' => __( 'Are you sure you want to delete this reading log?', 'book-database' ),
		'confirm_delete_series'      => __( 'Are you sure you want to delete this series?', 'book-database' ),
		'confirm_delete_taxonomy'    => __( 'Are you sure you want to delete this taxonomy?', 'book-database' ),
		'error_required_fields'      => esc_html__( 'Please fill out all the required fields.', 'book-database' ),
		'generic_error'              => esc_html__( 'An unexpected error has occurred.', 'book-database' ),
		'is_admin'                   => is_admin(),
		'please_wait'                => esc_html__( 'Please wait...', 'book-database' ),
		'save'                       => esc_html__( 'Save', 'book-database' ),
		'stars'                      => esc_html__( 'Stars', 'book-database' )
	);

	wp_localize_script( 'book-database', 'bdbVars', $localized );

}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_assets' );