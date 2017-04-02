<?php
/**
 * Load Admin Assets
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Admin Scripts
 *
 * Adds all admin scripts and stylesheets to the admin panel.
 *
 * @param string $hook Currently loaded page.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_load_admin_scripts( $hook ) {
	if ( ! apply_filters( 'book-database/load-admin-scripts', bdb_is_admin_page(), $hook ) ) {
		return;
	}

	$js_dir  = BDB_URL . 'assets/js/';
	$css_dir = BDB_URL . 'assets/css/';
	$screen  = get_current_screen();

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// CSS
	wp_enqueue_style( 'book-database', $css_dir . 'admin' . $suffix . '.css', array(), time() ); // @todo change to BDB_VERSION

	// JS
	$deps = array( 'jquery', 'jquery-ui-sortable', 'suggest' );

	// Only add recopy on settings page.
	if ( 'book-library_page_bdb-settings' == $screen->id ) {
		wp_enqueue_script( 'recopy', $js_dir . 'admin/jquery.recopy' . $suffix . '.js', $deps, '1.1.0', true );
		$deps[] = 'recopy';
	}

	// Only add analytics on analytics page.
	if ( 'book-library_page_bdb-analytics' == $screen->id ) {
		wp_enqueue_script( 'bookdb-analytics', $js_dir . 'admin/analytics' . $suffix . '.js', array( 'jquery' ), time(), true ); // @todo change to BDB_VERSION

		wp_localize_script( 'bookdb-analytics', 'bookdb_analytics', array(
			'l10n' => array(
				'average_rating' => esc_html__( 'Average Rating', 'book-database' ),
				'book'           => esc_html__( 'Book', 'book-database' ),
				'books_read'     => esc_html__( 'Books Read', 'book-database' ),
				'date'           => esc_html__( 'Date', 'book-database' ),
				'edit_book'      => esc_attr__( 'Edit Book', 'book-database' ),
				'edit_review'    => esc_attr__( 'Edit Review', 'book-database' ),
				'name'           => esc_html__( 'Name', 'book-database' ),
				'number_books'   => esc_html__( 'Number of Books', 'book-database' ),
				'number_reviews' => esc_html__( 'Number of Reviews', 'book-database' ),
				'pages'          => esc_html__( 'Pages', 'book-database' ),
				'rating'         => esc_html__( 'Rating', 'book-database' ),
			)
		) );
	}

	wp_enqueue_script( 'book-database', $js_dir . 'admin/admin' . $suffix . '.js', $deps, time(), true ); // @todo change to BDB_VERSION

	wp_localize_script( 'book-database', 'book_database', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'book-database' ),
		'l10n'     => array(
			'reading_entry_remove'  => esc_html__( 'Are you sure you wish to permanently delete this entry?', 'book-database' ),
			'review_remove'         => esc_html__( 'Are you sure you wish to delete this review?', 'book-database' ),
			'error_removing_review' => esc_html__( 'Error: Review ID not found.', 'book-database' )
		)
	) );
}

add_action( 'admin_enqueue_scripts', 'bdb_load_admin_scripts' );

/**
 * Media Upload Scripts
 *
 * This is separate because we need the upload script on BDB admin pages
 * *and* on pages where the modal is used.
 *
 * @param string $hook Currently loaded page.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_load_media_upload_scripts( $hook ) {
	if ( ! apply_filters( 'book-database/load-admin-scripts', bdb_is_admin_page(), $hook ) && ! bdb_show_media_button() ) {
		return;
	}

	$js_dir = BDB_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_media();
	wp_enqueue_script( 'book-database-media-upload', $js_dir . 'admin/media-upload' . $suffix . '.js', array( 'jquery' ), BDB_VERSION, true );
}

add_action( 'admin_enqueue_scripts', 'bdb_load_media_upload_scripts' );