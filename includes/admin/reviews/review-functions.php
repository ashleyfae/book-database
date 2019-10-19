<?php
/**
 * Admin Review Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the URL for deleting a review
 *
 * @param int $review_id ID of the review to delete.
 *
 * @return string
 */
function get_delete_review_url( $review_id ) {
	return wp_nonce_url( get_reviews_admin_page_url( array(
		'bdb_action' => 'delete_review',
		'review_id'  => $review_id
	) ), 'bdb_delete_review' );
}