<?php
/**
 * Admin Author Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Exceptions\Exception;

/**
 * Add a new author
 */
function process_add_author() {

	if ( empty( $_POST['bdb_add_author_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_add_author_nonce'], 'bdb_add_author' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['name'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Author name is required.', 'book-database' ), 400 );
		}

		$args = array(
			'name'        => $_POST['name'] ?? '',
			'slug'        => $_POST['slug'] ?? '',
			'description' => $_POST['description'] ?? '',
			'image_id'    => $_POST['image_id'] ?? null,
		);

		$author_id = add_book_author( $args );

		$edit_url = get_authors_admin_page_url( array(
			'view'        => 'edit',
			'author_id'   => $author_id,
			'bdb_message' => 'author_added',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_add_author' );

/**
 * Update an author
 */
function process_update_author() {

	if ( empty( $_POST['bdb_update_author_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_update_author_nonce'], 'bdb_update_author' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['author_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing author ID.', 'book-database' ), 400 );
		}

		if ( empty( $_POST['name'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Author name is required.', 'book-database' ), 400 );
		}

		$args = array(
			'name'        => $_POST['name'] ?? '',
			'slug'        => $_POST['slug'] ?? '',
			'description' => $_POST['description'] ?? '',
			'image_id'    => $_POST['image_id'] ?? null,
		);

		update_book_author( absint( $_POST['author_id'] ), $args );

		$edit_url = get_authors_admin_page_url( array(
			'view'        => 'edit',
			'author_id'   => absint( $_POST['author_id'] ),
			'bdb_message' => 'author_updated',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_update_author' );

/**
 * Process deleting an author
 */
function process_delete_author() {

	if ( empty( $_GET['bdb_action'] ) || 'delete_author' !== $_GET['bdb_action'] ) {
		return;
	}

	try {

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bdb_delete_author' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_GET['author_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing author ID.', 'book-database' ), 400 );
		}

		delete_book_author( absint( $_GET['author_id'] ) );

		wp_safe_redirect( get_authors_admin_page_url( array(
			'bdb_message' => 'author_deleted'
		) ) );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_delete_author' );
