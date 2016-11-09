<?php
/**
 * Review Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Below: Review Fields
 */

/*
 * Below: Saving Functions
 */

/**
 * Save Review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_save_review() {

	$nonce = isset( $_POST['bdb_save_review_nonce'] ) ? $_POST['bdb_save_review_nonce'] : false;

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'bdb_save_review' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to edit reviews.', 'book-database' ) );
	}

	$review_id = absint( $_POST['review_id'] );

	$review_data = array(
		'ID' => $review_id
	);

	// @todo

	$new_review_id = bdb_insert_review( $review_data );

	if ( ! $new_review_id || is_wp_error( $new_review_id ) ) {
		wp_die( __( 'An error occurred while inserting the review.', 'book-database' ) );
	}

	$edit_url = add_query_arg( array(
		'update-success' => 'true'
	), bdb_get_admin_page_edit_review( absint( $new_review_id ) ) );

	wp_safe_redirect( $edit_url );

	exit;

}

add_action( 'book-database/review/save', 'bdb_save_review' );