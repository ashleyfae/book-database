<?php
/**
 * Review Functions
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
 * Get reviews for a given post.
 *
 * @param int $post_id
 *
 * @since 1.0.0
 * @return array|false Array of review data.
 */
function bdb_get_post_reviews( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID(); // Use current ID if not specified.

	if ( ! $post_id ) {
		return false;
	}

	$reviews = book_database()->reviews->get_reviews( array( 'post_id' => $post_id ) );

	return $reviews;
}

/**
 * Get Total Number of Reviews
 *
 * @param array $args Array of arguments for the query.
 *
 * @since 1.0.0
 * @return int
 */
function bdb_count_total_reviews( $args ) {
	return book_database()->reviews->count( $args );
}