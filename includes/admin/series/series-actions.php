<?php
/**
 * Admin Series Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Add a new series
 */
function process_add_series() {

	if ( empty( $_POST['bdb_add_series_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_add_series_nonce'], 'bdb_add_series' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['name'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Series name is required.', 'book-database' ), 400 );
		}

		$args = array(
			'name'         => $_POST['name'] ?? '',
			'slug'         => $_POST['slug'] ?? '',
			'description'  => $_POST['description'] ?? '',
			'number_books' => $_POST['number_books'] ?? 1,
		);

		$series_id = add_book_series( $args );

		$edit_url = get_series_admin_page_url( array(
			'view'        => 'edit',
			'series_id'   => $series_id,
			'bdb_message' => 'series_added',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_add_series' );

/**
 * Update a series
 */
function process_update_series() {

	if ( empty( $_POST['bdb_update_series_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_update_series_nonce'], 'bdb_update_series' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['series_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing series ID.', 'book-database' ), 400 );
		}

		if ( empty( $_POST['name'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Series name is required.', 'book-database' ), 400 );
		}

		$args = array(
			'name'         => $_POST['name'] ?? '',
			'slug'         => $_POST['slug'] ?? '',
			'description'  => $_POST['description'] ?? '',
			'number_books' => $_POST['number_books'] ?? 1,
		);

		update_book_series( absint( $_POST['series_id'] ), $args );

		$edit_url = get_series_admin_page_url( array(
			'view'        => 'edit',
			'series_id'   => absint( $_POST['series_id'] ),
			'bdb_message' => 'series_updated',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_update_series' );

/**
 * Process deleting a series
 */
function process_delete_series() {

	if ( empty( $_GET['bdb_action'] ) || 'delete_series' !== $_GET['bdb_action'] ) {
		return;
	}

	try {

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bdb_delete_series' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_GET['series_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing series ID.', 'book-database' ), 400 );
		}

		delete_book_series( absint( $_GET['series_id'] ) );

		wp_safe_redirect( get_series_admin_page_url( array(
			'bdb_message' => 'series_deleted'
		) ) );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_delete_series' );