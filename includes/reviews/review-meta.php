<?php
/**
 * Review Meta
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get review meta
 *
 * @param int    $review_id ID of the review.
 * @param string $key       The meta key to retrieve the value of.
 * @param bool   $single    True to return a single result, false to return an array of results.
 *
 * @return mixed
 */
function get_review_meta( $review_id, $key = '', $single = true ) {
	return get_metadata( 'bdb_review', $review_id, $key, $single );
}

/**
 * Add review meta
 *
 * @param int    $review_id
 * @param string $meta_key
 * @param mixed  $meta_value
 * @param bool   $unique
 *
 * @return int|false ID of the meta on success, false on failure.
 */
function add_review_meta( $review_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'bdb_review', $review_id, $meta_key, $meta_value, $unique );
}

/**
 * Update review meta
 *
 * @param int    $review_id
 * @param string $meta_key
 * @param mixed  $meta_value
 * @param string $prev_value
 *
 * @return int|bool Meta ID if this was new meta, true if an existing value was updated, false on failure.
 */
function update_review_meta( $review_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'bdb_review', $review_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete meta from a review
 *
 * @param int    $review_id
 * @param string $meta_key
 * @param mixed  $meta_value
 *
 * @return bool
 */
function delete_review_meta( $review_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'bdb_review', $review_id, $meta_key, $meta_value );
}