<?php
/**
 * Admin Review Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Add a new review
 */
function process_add_review() {

	if ( empty( $_POST['bdb_add_review_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_add_review_nonce'], 'bdb_add_review' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['book_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
		}

		$args = array(
			'book_id'        => absint( $_POST['book_id'] ),
			'reading_log_id' => absint( $_POST['reading_log_id'] ),
			'user_id'        => ! empty( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : get_current_user_id(),
			'post_id'        => ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null,
			'url'            => ! empty( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '',
			'review'         => ! empty( $_POST['review'] ) ? wp_kses_post( $_POST['review'] ) : '',
			'date_written'   => ! empty( $_POST['date_written'] ) ? get_gmt_from_date( $_POST['date_written'] ) : current_time( 'mysql', true ),
			'date_published' => ! empty( $_POST['date_published'] ) ? get_gmt_from_date( $_POST['date_published'] ) : null
		);

		$review_id = add_review( $args );

		$edit_url = get_reviews_admin_page_url( array(
			'view'        => 'edit',
			'review_id'   => $review_id,
			'bdb_message' => 'review_added',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_add_review' );

/**
 * Update a review
 */
function process_update_review() {

	if ( empty( $_POST['bdb_update_review_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_update_review_nonce'], 'bdb_update_review' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['review_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing review ID.', 'book-database' ), 400 );
		}

		$review_id = absint( $_POST['review_id'] );

		if ( empty( $_POST['book_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
		}

		$args = array(
			'book_id'        => absint( $_POST['book_id'] ),
			'reading_log_id' => absint( $_POST['reading_log_id'] ),
			'user_id'        => ! empty( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : get_current_user_id(),
			'post_id'        => ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null,
			'url'            => ! empty( $_POST['url'] ) ? esc_url_raw( $_POST['url'] ) : '',
			'review'         => ! empty( $_POST['review'] ) ? wp_kses_post( $_POST['review'] ) : '',
			'date_written'   => ! empty( $_POST['date_written'] ) ? get_gmt_from_date( $_POST['date_written'] ) : current_time( 'mysql', true ),
			'date_published' => ! empty( $_POST['date_published'] ) ? get_gmt_from_date( $_POST['date_published'] ) : null
		);

		update_review( $review_id, $args );

		$edit_url = get_reviews_admin_page_url( array(
			'view'        => 'edit',
			'review_id'   => $review_id,
			'bdb_message' => 'review_updated',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_update_review' );

/**
 * Process deleting a review
 */
function process_delete_review() {

	if ( empty( $_GET['bdb_action'] ) || 'delete_review' !== $_GET['bdb_action'] ) {
		return;
	}

	try {

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bdb_delete_review' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_GET['review_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing review ID.', 'book-database' ), 400 );
		}

		delete_review( absint( $_GET['review_id'] ) );

		wp_safe_redirect( get_reviews_admin_page_url( array(
			'bdb_message' => 'review_deleted'
		) ) );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_delete_review' );