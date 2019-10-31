<?php
/**
 * Book Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Add a new book
 */
function process_add_book() {

	if ( empty( $_POST['bdb_add_book_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_add_book_nonce'], 'bdb_add_book' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['title'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Book title is required.', 'book-database' ), 400 );
		}

		$args = array(
			'cover_id'        => ! empty( $_POST['cover_id'] ) ? absint( $_POST['cover_id'] ) : null,
			'title'           => $_POST['title'] ?? '',
			'series_position' => $_POST['series_position'] ?? null,
			'pub_date'        => ! empty( $_POST['pub_date'] ) ? date( 'Y-m-d', strtotime( $_POST['pub_date'] ) ) : null,
			'pages'           => ( ! isset( $_POST['pages'] ) || '' === $_POST['pages'] ) ? null : absint( $_POST['pages'] ),
			'synopsis'        => ! empty( $_POST['synopsis'] ) ? wp_kses_post( $_POST['synopsis'] ) : '',
			'goodreads_url'   => ! empty( $_POST['goodreads_url'] ) ? esc_url_raw( $_POST['goodreads_url'] ) : '',
			'buy_link'        => ! empty( $_POST['buy_link'] ) ? esc_url_raw( $_POST['buy_link'] ) : '',
			'terms'           => array()
		);

		// Set the index title.
		if ( ! empty( $_POST['index_title'] ) && 'original' !== $_POST['index_title'] ) {
			$args['index_title'] = ( 'custom' != $_POST['index_title'] ) ? $_POST['index_title'] : $_POST['index_title_custom'];
		} elseif ( ! empty( $_POST['index_title'] ) && 'original' === $_POST['index_title'] ) {
			$args['index_title'] = $args['title'];
		}

		// Set the authors.
		if ( ! empty( $_POST['authors'] ) ) {
			$authors_array   = is_array( $_POST['authors'] ) ? $_POST['authors'] : explode( ',', $_POST['authors'] );
			$authors_array   = array_unique( array_map( 'trim', $authors_array ) );
			$args['authors'] = $authors_array;
		}

		// Set the series.
		if ( ! empty( $_POST['series_name'] ) ) {
			$series = get_book_series_by( 'name', sanitize_text_field( $_POST['series_name'] ) );

			if ( $series instanceof Series ) {
				$args['series_id'] = $series->get_id();
			} else {
				// Create a new series.
				$series_id = add_book_series( array(
					'name' => sanitize_text_field( $_POST['series_name'] )
				) );

				$args['series_id'] = $series_id;
			}
		}

		// Set the terms.
		if ( ! empty( $_POST['book_terms'] ) && is_array( $_POST['book_terms'] ) ) {
			foreach ( $_POST['book_terms'] as $taxonomy => $term_string ) {
				$taxonomy                   = sanitize_key( $taxonomy );
				$term_array                 = is_array( $term_string ) ? $term_string : explode( ',', $term_string );
				$term_array                 = array_unique( array_map( 'trim', $term_array ) );
				$args['terms'][ $taxonomy ] = $term_array;
			}
		}

		$book_id = add_book( $args );

		$edit_url = get_books_admin_page_url( array(
			'view'        => 'edit',
			'book_id'     => $book_id,
			'bdb_message' => 'book_added',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_add_book' );

/**
 * Update a book
 */
function process_update_book() {

	if ( empty( $_POST['bdb_update_book_nonce'] ) ) {
		return;
	}

	try {

		if ( ! wp_verify_nonce( $_POST['bdb_update_book_nonce'], 'bdb_update_book' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_POST['book_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing book ID.', 'book-database' ), 400 );
		}

		$book_id = absint( $_POST['book_id'] );

		if ( empty( $_POST['title'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Book title is required.', 'book-database' ), 400 );
		}

		$args = array(
			'cover_id'        => ! empty( $_POST['cover_id'] ) ? absint( $_POST['cover_id'] ) : null,
			'title'           => $_POST['title'] ?? '',
			'series_position' => $_POST['series_position'] ?? null,
			'pub_date'        => ! empty( $_POST['pub_date'] ) ? date( 'Y-m-d', strtotime( $_POST['pub_date'] ) ) : null,
			'pages'           => ( ! isset( $_POST['pages'] ) || '' === $_POST['pages'] ) ? null : absint( $_POST['pages'] ),
			'synopsis'        => ! empty( $_POST['synopsis'] ) ? wp_kses_post( $_POST['synopsis'] ) : '',
			'goodreads_url'   => ! empty( $_POST['goodreads_url'] ) ? esc_url_raw( $_POST['goodreads_url'] ) : '',
			'buy_link'        => ! empty( $_POST['buy_link'] ) ? esc_url_raw( $_POST['buy_link'] ) : ''
		);

		// Set the index title.
		if ( ! empty( $_POST['index_title'] ) && 'original' !== $_POST['index_title'] ) {
			$args['index_title'] = ( 'custom' != $_POST['index_title'] ) ? $_POST['index_title'] : $_POST['index_title_custom'];
		} elseif ( ! empty( $_POST['index_title'] ) && 'original' === $_POST['index_title'] ) {
			$args['index_title'] = $args['title'];
		}

		// Set the series.
		if ( ! empty( $_POST['series_name'] ) ) {
			$series = get_book_series_by( 'name', sanitize_text_field( $_POST['series_name'] ) );

			if ( $series instanceof Series ) {
				$args['series_id'] = $series->get_id();
			} else {
				// Create a new series.
				$series_id = add_book_series( array(
					'name' => sanitize_text_field( $_POST['series_name'] )
				) );

				$args['series_id'] = $series_id;
			}
		} else {
			$args['series_id'] = null;
		}

		// Update the book.
		update_book( $book_id, $args );

		// Set the authors.
		if ( ! empty( $_POST['authors'] ) ) {
			$authors_array = is_array( $_POST['authors'] ) ? $_POST['authors'] : explode( ',', $_POST['authors'] );
			$authors_array = array_unique( array_map( 'trim', $authors_array ) );

			set_book_authors( $book_id, $authors_array );
		}

		// Set the terms.
		if ( ! empty( $_POST['book_terms'] ) && is_array( $_POST['book_terms'] ) ) {
			foreach ( $_POST['book_terms'] as $taxonomy => $term_string ) {
				$taxonomy   = sanitize_key( $taxonomy );
				$term_array = is_array( $term_string ) ? $term_string : explode( ',', $term_string );
				$term_array = array_unique( array_map( 'trim', $term_array ) );

				set_book_terms( $book_id, $term_array, $taxonomy );
			}
		}

		$edit_url = get_books_admin_page_url( array(
			'view'        => 'edit',
			'book_id'     => $book_id,
			'bdb_message' => 'book_updated',
		) );

		wp_safe_redirect( $edit_url );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_update_book' );

/**
 * Process deleting a book
 */
function process_delete_book() {

	if ( empty( $_GET['bdb_action'] ) || 'delete_book' !== $_GET['bdb_action'] ) {
		return;
	}

	try {

		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'bdb_delete_book' ) || ! user_can_edit_books() ) {
			throw new Exception( 'permission_denied', __( 'You do not have permission to perform this action.', 'book-database' ), 403 );
		}

		if ( empty( $_GET['book_id'] ) ) {
			throw new Exception( 'missing_required_parameter', __( 'Missing book ID.', 'book-database' ), 400 );
		}

		delete_book( absint( $_GET['book_id'] ) );

		wp_safe_redirect( get_books_admin_page_url( array(
			'bdb_message' => 'book_deleted'
		) ) );
		exit;

	} catch ( Exception $e ) {
		wp_die( $e->getMessage() );
	}

}

add_action( 'admin_init', __NAMESPACE__ . '\process_delete_book' );