<?php
/**
 * Book Term Relationship Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Database\BookTerm\BookTermRelationshipQuery;
use Book_Database\Exceptions\Exception;
use Book_Database\Models\BookTerm;
use Book_Database\Models\BookTermRelationship;

/**
 * Get a single book-term-relationship by its ID
 *
 * @param int $relationship_id
 *
 * @return BookTermRelationship|false
 */
function get_book_term_relationship( $relationship_id ) {

	$query = new BookTermRelationshipQuery();

	return $query->get_item( $relationship_id );

}

/**
 * Get a book-term-relationship by book ID and term ID.
 *
 * @param int   $book_id ID of the book.
 * @param int   $term_id ID of the term.
 * @param array $args    Query arguments to override the defaults.
 *
 * @return BookTermRelationship|false|mixed
 */
function get_book_term_relationship_by_book_and_term( $book_id, $term_id, $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number'  => 1,
		'term_id' => $term_id,
		'book_id' => $book_id
	) );

	$relationships = get_book_term_relationships( $args );

	if ( empty( $relationships ) ) {
		return false;
	}

	return reset( $relationships );

}

/**
 * Query for book-term-relationships
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type int          $term_id             Filter by term ID. Default empty.
 * @type int          $book_id             Filter by book ID. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'term_id', 'book_id', 'date_created', and
 *                                         'date_modified'. Also accepts false, an empty array, or 'none'
 *                                          to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return BookTermRelationship[] Array of Book_Term_Relationship objects.
 */
function get_book_term_relationships( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new BookTermRelationshipQuery();

	return $query->query( $args );

}

/**
 * Count the book-term-relationships
 *
 * @param array $args
 *
 * @see get_book_term_relationships() for accepted arguments.
 *
 * @return int
 */
function count_book_term_relationships( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new BookTermRelationshipQuery( $args );

	return absint( $query->found_items );

}

/**
 * Add a new book-term-relationship
 *
 * @param array $args    {
 *
 * @type int    $term_id ID of the term.
 * @type int    $book_id ID of the book.
 * }
 *
 * @return int ID of the newly created relationship.
 * @throws Exception
 */
function add_book_term_relationship( $args ) {

	$args = wp_parse_args( $args, array(
		'term_id' => 0,
		'book_id' => 0
	) );

	if ( empty( $args['term_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A term ID is required.', 'book-database' ), 400 );
	}

	if ( empty( $args['book_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
	}

	$query           = new BookTermRelationshipQuery();
	$relationship_id = $query->add_item( $args );

	if ( empty( $relationship_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new relationship into the database.', 'book-database' ), 500 );
	}

	$relationship = get_book_term_relationship( $relationship_id );

	/**
	 * Triggers when a relationship is successfully added.
	 *
	 * @param int $relationship_id ID of the relationship.
	 * @param int $term_id         ID of the term.
	 * @param int $book_id         ID of the book.
	 */
	do_action( 'book-database/book-term-relationship/added', $relationship_id, $relationship->get_term_id(), $relationship->get_book_id() );

	return absint( $relationship_id );

}

/**
 * Delete a book-term-relationship
 *
 * @param int $relationship_id ID of the relationship to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_term_relationship( $relationship_id ) {

	$relationship = get_book_term_relationship( $relationship_id );

	if ( ! $relationship instanceof BookTermRelationship ) {
		return true;
	}

	$query   = new BookTermRelationshipQuery();
	$deleted = $query->delete_item( $relationship_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the relationship.', 'book-database' ), 500 );
	}

	/**
	 * Triggers when a relationship is successfully deleted.
	 *
	 * @param int $relationship_id ID of the relationship.
	 * @param int $term_id         ID of the term.
	 * @param int $book_id         ID of the book.
	 */
	do_action( 'book-database/book-term-relationship/deleted', $relationship_id, $relationship->get_term_id(), $relationship->get_book_id() );

	return true;

}

/**
 * Get the terms associated with a book
 *
 * @param int    $book_id  ID of the book to get the terms of.
 * @param string $taxonomy Taxonomy slug.
 * @param array  $args     {
 *                         Query arguments to override the defaults.
 *
 * @type string  $orderby  Column to order by.
 * @type string  $order    Order.
 * @type string  $fields   Column names to return.
 * }
 *
 * @return BookTerm[]|array
 */
function get_attached_book_terms( $book_id, $taxonomy, $args = array() ) {

	global $wpdb;

	$args = wp_parse_args( $args, array(
		'orderby' => 'name',
		'order'   => 'ASC',
		'fields'  => ''
	) );

	$relationships_table = book_database()->get_table( 'book_term_relationships' )->get_table_name();
	$term_table          = book_database()->get_table( 'book_terms' )->get_table_name();

	$where_taxonomy = $taxonomy ? $wpdb->prepare( " AND t.taxonomy = %s ", sanitize_text_field( $taxonomy ) ) : '';

	// Select this.
	$select_this = 't.*';
	if ( in_array( $args['fields'], array( 'id', 'ids' ) ) ) {
		$select_this = 't.id';
	} elseif ( in_array( $args['fields'], array( 'name', 'names' ) ) ) {
		$select_this = 't.name';
	}

	// Orderby
	$orderby = $args['orderby'];
	$order   = $args['order'];

	if ( in_array( $orderby, array( 'id', 'taxonomy', 'name', 'slug', 'count', 'date_created', 'date_modified' ) ) ) {
		$orderby = "t.$orderby";
	} elseif ( 'none' === $orderby ) {
		$orderby = '';
		$order   = '';
	} else {
		$orderby = 't.id';
	}

	if ( ! empty( $orderby ) ) {
		$orderby = "ORDER BY $orderby";
	}

	$order = strtoupper( $order );
	if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
		$order = 'ASC';
	}

	$query = $wpdb->prepare( "SELECT {$select_this} FROM {$term_table} AS t INNER JOIN {$relationships_table} AS tr on t.id = tr.term_id WHERE tr.book_id = %d $where_taxonomy $orderby $order", absint( $book_id ) );
	$terms = array();

	if ( empty( $args['fields'] ) ) {
		$objects = $wpdb->get_results( $query );

		if ( is_array( $objects ) ) {
			foreach ( $objects as $object ) {
				$terms[] = new BookTerm( $object );
			}
		}
	} elseif ( in_array( $args['fields'], array( 'id', 'ids', 'name', 'names' ) ) ) {
		$terms = $wpdb->get_col( $query );
	}

	return $terms;

}

/**
 * Create book and term relationships
 *
 * @param int              $book_id  ID of the book to add terms to.
 * @param array|int|string $terms    Single term name/ID, or array of term names/IDs.
 * @param string           $taxonomy Taxonomy slug.
 * @param bool             $append   False to delete the difference of terms, true to append to the existing.
 *
 * @throws Exception
 */
function set_book_terms( $book_id, $terms, $taxonomy, $append = false ) {

	global $wpdb;

	if ( ! is_array( $terms ) ) {
		$terms = array( $terms );
	}

	// Get existing terms.
	if ( ! $append ) {
		$old_term_ids = get_attached_book_terms( $book_id, $taxonomy, array( 'fields' => 'id' ) );
	} else {
		$old_term_ids = array();
	}

	$all_term_ids = array();

	foreach ( $terms as $term ) {

		// $term is either a term name or ID

		if ( ! strlen( trim( $term ) ) ) {
			continue;
		}

		if ( is_int( $term ) ) {

			// We have a term ID.

			$term_id = absint( $term );

			// If this term doesn't exist - skip it.
			if ( ! get_book_term( $term_id ) ) {
				continue;
			}

			$all_term_ids[] = $term_id;

		} else {

			// We have a term name.

			// Check to see if it already exists.
			$term_id = get_book_term_by_name_and_taxonomy( $term, $taxonomy, array( 'fields' => 'id' ) );

			if ( empty( $term_id ) ) {
				// Create a new term.
				$term_id = add_book_term( array(
					'name'     => $term,
					'taxonomy' => $taxonomy,
					'count'    => 0
				) );
			}

			if ( empty( $term_id ) ) {
				continue;
			}

			$all_term_ids[] = $term_id;

		}

		// If the term relationship already exists, let's move on.
		if ( get_book_term_relationship_by_book_and_term( $book_id, $term_id, array( 'fields' => 'id' ) ) ) {
			continue;
		}

		// Otherwise, create the relationship.
		add_book_term_relationship( array(
			'term_id' => absint( $term_id ),
			'book_id' => absint( $book_id )
		) );

	}

	if ( ! $append ) {

		// Delete the differing relationships.
		$delete_term_ids = array_diff( $old_term_ids, $all_term_ids );

		if ( $delete_term_ids ) {
			$relationship_table = book_database()->get_table( 'book_term_relationships' )->get_table_name();
			$term_id_string     = implode( ',', array_map( 'absint', $delete_term_ids ) );

			$query = $wpdb->prepare( "DELETE FROM {$relationship_table} WHERE book_id = %d AND term_id IN({$term_id_string})", $book_id );
			$wpdb->query( $query );
		}

	}

}
