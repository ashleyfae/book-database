<?php
/**
 * Book Term Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single book term by its ID
 *
 * @param int $term_id
 *
 * @return Book_Term|false
 */
function get_book_term( $term_id ) {

	$query = new Book_Terms_Query();

	return $query->get_item( $term_id );

}

/**
 * Get a single book term by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Book_Term|false
 */
function get_book_term_by( $column_name, $column_value ) {

	$query = new Book_Terms_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Get a single book term by name and taxonomy.
 *
 * @param string $term_name Term name.
 * @param string $taxonomy  Taxonomy slug.
 *
 * @return Book_Term|false
 */
function get_book_term_by_name_and_taxonomy( $term_name, $taxonomy, $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number'   => 1,
		'taxonomy' => $taxonomy,
		'name'     => $term_name
	) );

	$terms = get_book_terms( $args );

	if ( empty( $terms ) ) {
		return false;
	}

	return reset( $terms );

}

/**
 * Query for book terms
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type string       $taxonomy            Filter by taxonomy slug. Default empty.
 * @type array        $taxonomy__in        An array of taxonomy slugs to include. Default empty.
 * @type array        $taxonomy__not_in    An array of taxonomy slugs to exclude. Default empty.
 * @type string       $name                Filter by name. Default empty.
 * @type string       $slug                Filter by slug. Default empty.
 * @type int          $image_id            Filter by image ID. Default empty.
 * @type int          $count               Filter by count. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'taxonomy', 'name', 'slug', 'image_id',
 *                                         'count', 'date_created', and 'date_modified'. Also accepts false,
 *                                         an empty array, or 'none' to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Book_Term[] Array of Book_Term objects.
 */
function get_book_terms( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Book_Terms_Query();

	return $query->query( $args );

}

/**
 * Count the book terms
 *
 * @param array $args
 *
 * @see get_book_terms() for accepted arguments.
 *
 * @return int
 */
function count_book_terms( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Book_Terms_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new book term
 *
 * @param array $args        {
 *
 * @type string $taxonomy    Taxonomy slug.
 * @type string $name        Term name.
 * @type string $slug        Term slug. If omitted, it will be auto generated from the name.
 * @type string $description Term description.
 * @type int    $image_id    ID of the attachment.
 * @type string $links       Term links.
 * @type int    $count       Number of books associated with this term.
 * }
 *
 * @return int
 * @throws Exception
 */
function add_book_term( $args ) {

	$args = wp_parse_args( $args, array(
		'taxonomy'    => '',
		'name'        => '',
		'slug'        => '',
		'description' => '',
		'image_id'    => 0,
		'links'       => '',
		'count'       => 0
	) );

	// Taxonomy and name are required.
	if ( empty( $args['taxonomy'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A taxonomy slug is required.', 'book-database' ), 400 );
	}

	if ( empty( $args['name'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A term name is required.', 'book-database' ), 400 );
	}

	// Generate a slug.
	$args['slug'] = ! empty( $args['slug'] ) ? unique_book_slug( $args['slug'], $args['taxonomy'] ) : unique_book_slug( sanitize_title( $args['name'], $args['taxonomy'] ) );

	$query   = new Book_Terms_Query();
	$term_id = $query->add_item( $args );

	if ( empty( $term_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new term into the database.', 'book-database' ), 500 );
	}

	return absint( $term_id );

}

/**
 * Update an existing term
 *
 * @param int   $term_id
 * @param array $args
 */
function update_book_term( $term_id, $args = array() ) {

	$term = get_book_term( $term_id );

	if ( empty( $term ) ) {
		throw new Exception( 'invalid_id', __( 'Invalid term ID.', 'book-database' ), 400 );
	}

	// Taxonomies cannot be changed.
	if ( isset( $args['taxonomy'] ) ) {
		throw new Exception( 'invalid_parameter', __( 'Term taxonomies cannot be changed.', 'book-database' ), 400 );
	}

	// If the slug is changing, let's re-generate it.
	if ( isset( $args['slug'] ) && $args['slug'] != $term->get_slug() ) {
		$args['slug'] = unique_book_slug( $args['slug'], $term->get_taxonomy() );
	}

	$query   = new Book_Terms_Query();
	$updated = $query->update_item( $term_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the term.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a term
 *
 * This will also delete all the book-term-relationship records for this term.
 *
 * @param int $term_id ID of the term to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_term( $term_id ) {

	$query   = new Book_Taxonomies_Query();
	$deleted = $query->delete_item( $term_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the term.', 'book-database' ), 500 );
	}

	/**
	 * @var int[] $relationships
	 */
	$relationships = get_book_term_relationships( array(
		'term_id' => $term_id,
		'fields'  => 'id'
	) );

	if ( $relationships ) {
		foreach ( $relationships as $relationship_id ) {
			delete_book_term_relationship( $relationship_id );
		}
	}

	return true;

}

/**
 * Recalculate and update the count for a book term
 *
 * @param int $term_id ID of the term to update the count of.
 *
 * @return bool True on success, false on failure.
 */
function recalculate_book_term_count( $term_id ) {

	$new_count = count_book_term_relationships( array(
		'term_id' => $term_id
	) );

	try {
		update_book_term( $term_id, array(
			'count' => absint( $new_count )
		) );

		return true;
	} catch ( Exception $e ) {
		return false;
	}

}