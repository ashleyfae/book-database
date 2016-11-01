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
 * @param string $hook Currently loaded page
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

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// CSS
	wp_enqueue_style( 'book-database', $css_dir . 'admin' . $suffix . '.css', array(), BDB_VERSION );

	// JS
	$deps = array( 'jquery', 'jquery-ui-sortable' );
	wp_enqueue_media();
	wp_enqueue_script( 'book-database', $js_dir . 'admin' . $suffix . '.js', $deps, BDB_VERSION, true );
}

add_action( 'admin_enqueue_scripts', 'bdb_load_admin_scripts' );