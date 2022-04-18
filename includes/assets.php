<?php
/**
 * Front-end Assets
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Load assets
 */
function load_assets() {

	/**
	 * Filters whether or not assets should be loaded.
	 *
	 * @param bool $load_assets
	 */
	$load_assets = apply_filters( 'book-database/load-assets', true );

	if ( ! $load_assets ) {
		return;
	}

	$css_dir = BDB_URL . 'assets/css/';

	wp_enqueue_style( 'book-database', $css_dir . 'front-end.css', array(), BDB_VERSION );

}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_assets' );
