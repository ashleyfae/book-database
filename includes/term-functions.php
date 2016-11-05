<?php
/**
 * Term Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Term Display Types
 *
 * How terms can be displayed in the admin area.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_term_display_types() {
	$types = array(
		'text'     => esc_html__( 'Text', 'book-database' ),
		'checkbox' => esc_html__( 'Checkbox', 'book-database' )
	);

	return apply_filters( 'book-database/term-display-types', $types );
}

/**
 * Get Terms
 *
 * @param array $args Arguments to override the defaults.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_terms( $args = array() ) {
	$terms = book_database()->book_terms->get_terms( $args );

	return $terms;
}

/**
 * Get Book Terms
 *
 * @see   wp_get_object_terms()
 *
 * @param int         $book_id ID of the book to get the terms for.
 * @param string|bool $type    Type of terms to retrieve, or false for all.
 * @param array       $args    Query arguments to override the defaults.
 *
 * @since 1.0.0
 * @return array|false Array of term objects or false on failure.
 */
function bdb_get_book_terms( $book_id, $type = false, $args = array() ) {
	global $wpdb;

	$default_args = array(
		'orderby' => 'name',
		'order'   => 'ASC',
		'fields'  => 'all'
	);

	$args = wp_parse_args( $args, $default_args );

	$relationship_table = $wpdb->prefix . 'bdb_book_term_relationships';
	$term_table         = $wpdb->prefix . 'bdb_book_terms';

	$where_type = $type ? $wpdb->prepare( " AND t.type = %s", sanitize_text_field( $type ) ) : '';

	// Select this.
	$select_this = '';
	if ( 'all' == $args['fields'] ) {
		$select_this = 't.*';
	} elseif ( 'ids' == $args['fields'] ) {
		$select_this = 't.term_id';
	} elseif ( 'names' == $args['fields'] ) {
		$select_this = 't.name';
	}

	// Orderby
	$orderby = $args['orderby'];
	$order   = $args['order'];

	if ( in_array( $orderby, array( 'term_id', 'type', 'name', 'count' ) ) ) {
		$orderby = "t.$orderby";
	} elseif ( 'none' === $orderby ) {
		$orderby = '';
		$order   = '';
	} else {
		$orderby = 't.term_id';
	}

	if ( ! empty( $orderby ) ) {
		$orderby = "ORDER BY $orderby";
	}

	$order = strtoupper( $order );
	if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
		$order = 'ASC';
	}

	$query = $wpdb->prepare( "SELECT $select_this FROM $term_table AS t INNER JOIN $relationship_table AS tr on t.term_id = tr.term_id WHERE tr.book_id = %d $where_type $orderby $order", absint( $book_id ) );
	$terms = array();

	if ( 'all' == $args['fields'] ) {
		$terms = $wpdb->get_results( $query );
	} elseif ( 'ids' == $args['fields'] || 'names' == $args['fields'] ) {
		$terms = $wpdb->get_col( $query );
	}

	return $terms;
}

/**
 * Create Book and Term Relationships
 *
 * Relates a book to a term and term type. Creates the
 * term if it doesn't already exist.
 *
 * @see   wp_set_object_terms()
 *
 * @param int              $book_id ID of the book to relate terms to.
 * @param array|int|string $terms   Single term ID or array of IDs.
 * @param string           $type    Term type (`author`, `publisher`, etc.).
 * @param bool             $append  If false, will delete the difference of terms.
 *
 * @since 1.0.0
 * @return array|WP_Error Term IDs of the affected terms.
 */
function bdb_set_book_terms( $book_id, $terms, $type, $append = false ) {

	if ( ! is_numeric( $book_id ) || ! $book_id > 0 ) {
		return new WP_Error( 'invalid_book_id', __( 'Invalid book ID.', 'book-database' ) );
	}

	if ( ! is_array( $terms ) ) {
		$terms = array( $terms );
	}

	// Get existing terms.
	if ( ! $append ) {
		$old_term_ids = bdb_get_book_terms( $book_id, $type, array( 'fields' => 'ids' ) );
	} else {
		$old_term_ids = array();
	}

	$all_term_ids = array();

	foreach ( (array) $terms as $term ) {

		// $term is either a term name or ID.

		if ( ! strlen( trim( $term ) ) ) {
			continue;
		}

		if ( is_int( $term ) ) {

			// We have a term ID.

			$term_id = absint( $term );

			// If this term ID doesn't exist - skip it.
			if ( ! book_database()->book_terms->exists( $term_id ) ) {
				continue;
			}

			// Update the count.
			bdb_update_term_count( $term_id );

			$all_term_ids[] = $term_id;

		} else {

			// We have a term name.

			// Check to see if it already exists.
			$existing_term = book_database()->book_terms->get_term_by( 'name', $term );

			if ( $existing_term ) {
				$term_id = $existing_term->term_id;
			} else {
				// Create new term.
				$term_id = book_database()->book_terms->add( array(
					'name' => sanitize_text_field( $term ),
					'type' => sanitize_text_field( $type )
				) );
			}

			// Error adding it.
			if ( ! $term_id ) {
				continue;
			}

			$all_term_ids[] = $term_id;

		}

		// If the term relationship already exists - let's move on.
		if ( bdb_relationship_exists( $book_id, $term_id ) ) {
			continue;
		}

		// Otherwise, create the relationship.
		book_database()->book_term_relationships->add( array(
			'term_id' => absint( $term_id ),
			'book_id' => absint( $book_id ),
			'count'   => 1
		) );

	}

	if ( ! $append ) {

		// Delete existing term relationships.
		$delete_term_relationships = array_diff( $old_term_ids, $all_term_ids );

		if ( $delete_term_relationships ) {
			foreach ( $delete_term_relationships as $term_id ) {
				// Delete the relationship.
				book_database()->book_term_relationships->delete( absint( $term_id ) ); // @todo do this without a foreach

				// Reduce the count.
				bdb_update_term_count( $term_id );
			}
		}

	}

	return $all_term_ids;

}

/**
 * Relationship Exists
 *
 * Checks whether a relationship exists between a book ID and a term ID.
 *
 * @param int $book_id
 * @param int $term_id
 *
 * @since 1.0.0
 * @return bool
 */
function bdb_relationship_exists( $book_id, $term_id ) {
	if ( ! is_numeric( $book_id ) || ! is_numeric( $term_id ) ) {
		return false;
	}

	$args   = array(
		'term_id' => absint( $term_id ),
		'book_id' => absint( $book_id )
	);
	$result = book_database()->book_term_relationships->get_relationships( apply_filters( 'book-database/relationship-exists-args', $args, $book_id, $term_id ) );

	return ( is_array( $result ) && count( $result ) ) ? true : false;
}

/**
 * Update Term Count
 *
 * @param int $term_id ID of the term.
 *
 * @since 1.0.0
 * @return int|bool Updated term ID on success, or false on failure.
 */
function bdb_update_term_count( $term_id ) {
	$new_count = book_database()->book_term_relationships->count( array(
		'term_id' => $term_id
	) );

	if ( false === $new_count ) {
		return false;
	}

	$args = array(
		'term_id' => absint( $term_id ),
		'count'   => absint( $new_count )
	);

	return book_database()->book_terms->add( $args );
}