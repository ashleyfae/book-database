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

/**
 * Field: Associated Book
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_book_id_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Book ID', 'book-database' ) ), array(
		'id'    => 'associated_book',
		'name'  => 'associated_book',
		'value' => $review->get_book_id(),
		'type'  => 'number',
		'desc'  => __( 'The book this is a review of. Enter the book ID number.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_book_id_field' );

/**
 * Field: Associated Post
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_post_id_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Associated Post ID', 'book-database' ) ), array(
		'id'    => 'associated_post',
		'name'  => 'associated_post',
		'value' => $review->get_post_id(),
		'type'  => 'number',
		'desc'  => __( 'Leave blank to not associate this review with a blog post.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_post_id_field' );

/**
 * Field: URL
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_url_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'External Review URL', 'book-database' ) ), array(
		'id'          => 'external_url',
		'name'        => 'external_url',
		'value'       => $review->is_external() ? $review->get_url() : false,
		'type'        => 'url',
		'placeholder' => 'http://',
		'desc'        => __( 'Enter a URL to the external review location. Leave blank if this is a review associated with a blog post.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_url_field' );

/**
 * Field: User ID
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_user_id_field( $review ) {
	$current_user = wp_get_current_user();

	book_database()->html->meta_row( 'text', array( 'label' => __( 'Reviewer User ID', 'book-database' ) ), array(
		'id'    => 'review_user_id',
		'name'  => 'review_user_id',
		'value' => false !== $review->get_user_id() ? $review->get_user_id() : $current_user->ID,
		'type'  => 'number',
		'desc'  => __( 'ID of the user reviewing the book. Default is your user ID.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_user_id_field' );

/**
 * Field: Rating
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_rating_field( $review ) {
	book_database()->html->meta_row( 'rating_dropdown', array( 'label' => __( 'Rating', 'book-database' ) ), array(
		'id'       => 'book_rating',
		'name'     => 'book_rating',
		'selected' => $review->get_rating()
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_rating_field' );

/**
 * Field: Date Added
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_date_added_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Date', 'book-database' ) ), array(
		'id'    => 'review_date',
		'name'  => 'review_date',
		'value' => false !== $review->get_date() ? $review->get_formatted_date() : '',
		'type'  => 'text',
		'desc'  => __( 'Date of the review. Leave blank to use today\'s date. You only need to fill this out if entering back-dated reviews.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_date_added_field' );

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

	$fields = array(
		'book_id' => 'associated_book',
		'post_id' => 'associated_post',
		'url'     => 'external_url',
		'user_id' => 'review_user_id',
		'rating'  => 'book_rating'
	);

	foreach ( $fields as $db_field => $post_field ) {
		if ( ! isset( $_POST[ $post_field ] ) ) {
			continue;
		}

		$review_data[ $db_field ] = $_POST[ $post_field ];
	}

	// Format the date.
	if ( isset( $_POST['review_date'] ) && ! empty( $_POST['review_date'] ) ) {
		$timestamp                 = strtotime( wp_strip_all_tags( $_POST['review_date'] ) );
		$review_data['date_added'] = date( 'Y-m-d H:i:s', $timestamp );
	}

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