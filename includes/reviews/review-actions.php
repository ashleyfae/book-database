<?php
/**
 * Review Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * When a post is published or scheduled, sync associated reviews with the post's publication date.
 *
 * @param string   $new_status
 * @param string   $old_status
 * @param \WP_Post $post
 *
 * @since 1.0
 */
function sync_review_publish_date( $new_status, $old_status, $post ) {

	if ( ! bdb_get_option( 'sync_published_date' ) ) {
		return;
	}

	if ( 'publish' === $new_status || 'future' === $new_status ) {

		// Find all reviews associated with this post.
		$reviews = get_reviews( array(
			'review_query' => array(
				array(
					'field' => 'post_id',
					'value' => absint( $post->ID )
				)
			)
		) );

		if ( empty( $reviews ) ) {
			return;
		}

		$post_date = date( 'Y-m-d H:i:s', strtotime( $post->post_date_gmt ) );

		foreach ( $reviews as $review ) {
			try {
				update_review( $review->id, array(
					'date_published' => $post_date
				) );
			} catch ( Exception $e ) {

			}
		}

	}

}

add_action( 'transition_post_status', __NAMESPACE__ . '\sync_review_publish_date', 10, 3 );