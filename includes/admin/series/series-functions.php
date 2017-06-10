<?php
/**
 * Admin Series Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Default Series Views
 *
 * @param array $views
 *
 * @since 1.0
 * @return array
 */
function bdb_register_default_series_views( $views ) {

	$default_views = array(
		'add'  => 'bdb_series_edit_view',
		'edit' => 'bdb_series_edit_view'
	);

	return array_merge( $views, $default_views );

}

add_filter( 'book-database/series/views', 'bdb_register_default_series_views' );