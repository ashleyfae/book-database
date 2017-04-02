<?php
/**
 * Ajax Callbacks Used in the Modal
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ajax CB: Get Book by ID
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_book() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_id = isset( $_POST['book_id'] ) ? absint( $_POST['book_id'] ) : 0;

	if ( ! $book_id ) {
		wp_send_json_error( __( 'Error: Invalid book ID.', 'book-database' ) );
	}

	$book = bdb_get_book( $book_id );

	if ( $data = $book->get_data() ) {

		$terms = bdb_get_all_book_terms( $book->ID );

		if ( $terms ) {
			$final_terms = array();
			$taxonomies  = bdb_get_option( 'taxonomies' );

			foreach ( $taxonomies as $taxonomy_options ) {
				if ( ! array_key_exists( $taxonomy_options['id'], $terms ) ) {
					continue;
				}

				$names = array();

				foreach ( $terms[ $taxonomy_options['id'] ] as $this_term ) {
					$names[] = $this_term->name;
				}

				if ( 'checkbox' == $taxonomy_options['display'] ) {
					$final_terms[ $taxonomy_options['id'] ] = $names;
				} else {
					$final_terms[ $taxonomy_options['id'] ] = implode( ', ', $names );
				}
			}

			$data['terms'] = $final_terms;
		}

	} else {
		$data = array();
	}

	wp_send_json_success( $data );

	exit;
}

add_action( 'wp_ajax_bdb_get_book', 'bdb_ajax_get_book' );

/**
 * Ajax CB: Update or Create Book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_save_book() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_id = isset( $_POST['book_id'] ) ? $_POST['book_id'] : 0;

	parse_str( $_POST['book_info'], $book_data );

	if ( $book_id ) {
		$book_data['ID'] = absint( $book_id );
	}

	// Move `book_terms` to `terms` and format.
	if ( array_key_exists( 'book_terms', $book_data ) && is_array( $book_data['book_terms'] ) ) {
		$terms = array();
		foreach ( $book_data['book_terms'] as $type => $term_string ) {
			$type = bdb_sanitize_key( $type );
			if ( is_array( $term_string ) ) {
				$term_array = $term_string;
			} else {
				$term_array = $term_string ? explode( ',', $term_string ) : array();
			}
			$terms[ $type ] = array_map( 'trim', $term_array );
		}
		$book_data['terms'] = $terms;
		unset( $book_data['book_terms'] );
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Error: You do not have permission to add books.', 'book-database' ) );
	}

	$new_book_id = bdb_insert_book( $book_data );

	wp_send_json_success( $new_book_id );
}

add_action( 'wp_ajax_bdb_save_book', 'bdb_ajax_save_book' );

/**
 * Ajax CB: Get Thumbnail URL from ID
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_thumbnail() {
	check_ajax_referer( 'book-database', 'nonce' );

	$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;
	$thumb    = wp_get_attachment_image_url( $image_id, 'medium' );

	wp_send_json_success( $thumb );

	exit;
}

add_action( 'wp_ajax_bdb_get_thumbnail', 'bdb_ajax_get_thumbnail' );

/**
 * Search for Existing Book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_search_book() {
	check_ajax_referer( 'book-database', 'nonce' );

	$search = isset( $_POST['search'] ) ? wp_strip_all_tags( $_POST['search'] ) : false;
	$field  = ( isset( $_POST['field'] ) && $_POST['field'] == 'author' ) ? 'author' : 'title';

	if ( ! $search ) {
		wp_send_json_error( __( 'Error: A search term is resquired.', 'book-database' ) );
	}

	$list_items = '';

	if ( 'author' == $field ) {
		$args = array(
			'author_name' => $search
		);
	} else {
		$args = array(
			'title' => $search
		);
	}

	$books = bdb_get_books( apply_filters( 'book-database/admin/books/search-book-args', $args, $search, $field ) );

	if ( ! is_array( $books ) || ! count( $books ) ) {
		wp_send_json_error( __( 'No results found.', 'book-database' ) );
	}

	foreach ( $books as $book ) {
		$author_name = isset( $book->author_name ) ? $book->author_name : bdb_get_book_author_name( $book->ID );
		$list_items .= '<li><a href="#" data-id="' . esc_attr( $book->ID ) . '">' . sprintf( __( '%s by %s', 'book-database' ), $book->title, $author_name ) . '</a></li>';
	}

	wp_send_json_success( '<ul>' . $list_items . '</ul>' );

	exit;
}

add_action( 'wp_ajax_bdb_search_book', 'bdb_ajax_search_book' );