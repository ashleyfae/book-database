<?php
/**
 * Admin Book Term Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Exceptions\Exception;

/**
 * Add a new book_term
 */
function process_add_book_term() {

	if ( empty( $_POST['bdb_add_book_term_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_add_book_term_nonce'], 'bdb_add_book_term' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['name'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Term name is required.', 'book-database' ), 400 );
		}

		if ( empty( $_POST['taxonomy'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'You must select a taxonomy.', 'book-database' ), 400 );
		}

		$args = array(
			'name'        => $_POST['name'] ?? '',
			'slug'        => $_POST['slug'] ?? '',
			'taxonomy'    => $_POST['taxonomy'] ?? '',
			'description' => $_POST['description'] ?? '',
		);

		$term_id = add_book_term( $args );

		$edit_url = get_book_terms_admin_page_url( array(
			'status'      => urlencode( $_POST['taxonomy'] ),
			'bdb_message' => 'term_added',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_add_book_term' );

/**
 * Update a book term
 */
function process_update_book_term() {

	if ( empty( $_POST['bdb_update_book_term_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_update_book_term_nonce'], 'bdb_update_book_term' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['term_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing term ID.', 'book-database' ), 400 );
		}

		if ( empty( $_POST['name'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Term name is required.', 'book-database' ), 400 );
		}

		$args = array(
			'name'        => $_POST['name'] ?? '',
			'slug'        => $_POST['slug'] ?? '',
			'description' => $_POST['description'] ?? '',
		);

		update_book_term( absint( $_POST['term_id'] ), $args );

		$edit_url = get_book_terms_admin_page_url( array(
			'view'        => 'edit',
			'term_id'     => absint( $_POST['term_id'] ),
			'bdb_message' => 'term_updated',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_update_book_term' );

/**
 * Process deleting a term
 */
function process_delete_book_term() {

	if ( empty( $_GET['bdb_action'] ) || 'delete_term' !== $_GET['bdb_action'] ) {
		return;
	}

	try {

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bdb_delete_term' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_GET['term_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing term ID.', 'book-database' ), 400 );
		}

		delete_book_term( absint( $_GET['term_id'] ) );

		wp_safe_redirect( get_book_terms_admin_page_url( array(
			'bdb_message' => 'term_deleted'
		) ) );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_delete_book_term' );
