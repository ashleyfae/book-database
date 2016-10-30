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
	$css_dir = BDB_URL . 'assets/css/'; // @todo change to css

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// @todo

	wp_enqueue_style( 'bookdb-admin', $css_dir . 'admin' . $suffix . '.css', array(), BDB_VERSION );
}

add_action( 'admin_enqueue_scripts', 'bdb_load_admin_scripts' );

/**
 * Load Admin Post Assets
 *
 * These assets only get loaded on the Add/Edit Post screen.
 *
 * @param string $hook
 *
 * @since 1.0.0
 * @return void
 */
function bdb_load_admin_post_assets( $hook ) {
	// @todo page check

	if ( $hook != 'post.php' && $hook != 'edit.php' ) {
		return;
	}

	$js_dir  = BDB_URL . 'assets/js/';
	$css_dir = BDB_URL . 'assets/css/'; // @todo change to css

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	wp_enqueue_style( 'bookdb-post-screen', $css_dir . 'post-screen' . $suffix . '.css', array(), BDB_VERSION );
}

add_action( 'admin_enqueue_scripts', 'bdb_load_admin_post_assets' );