<?php
/**
 * Admin Review Functions
 *
 * @package   review-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Default Book Views
 *
 * @param array $views
 *
 * @since 1.0.0
 * @return array
 */
function bdb_register_default_review_views( $views ) {

	$default_views = array(
		'add'  => 'bdb_reviews_edit_view',
		'edit' => 'bdb_reviews_edit_view'
	);

	return array_merge( $views, $default_views );

}

add_filter( 'book-database/reviews/views', 'bdb_register_default_review_views' );