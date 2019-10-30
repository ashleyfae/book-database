<?php
/**
 * Taxonomy Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single taxonomy by its ID
 *
 * @param int $taxonomy_id
 *
 * @return Book_Taxonomy|false
 */
function get_book_taxonomy( $taxonomy_id ) {

	$query = new Book_Taxonomies_Query();

	return $query->get_item( $taxonomy_id );

}

/**
 * Get a single taxonomy by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Book_Taxonomy|false
 */
function get_book_taxonomy_by( $column_name, $column_value ) {

	$query = new Book_Taxonomies_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for book taxonomies
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type string       $name                Filter by name. Default empty.
 * @type string       $slug                Filter by slug. Default empty.
 * @type array        $slug__in            Array of slugs to include. Default empty.
 * @type array        $slug__not_in        Array of slugs to exclude. Default empty.
 * @type string       $format              Filter by format. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'cover_id', 'title', 'index_title', 'series_id',
 *                                         'series_position', 'pub_date', 'pages', 'date_created', and
 *                                         'date_modified'. Also accepts false, an empty array, or 'none'
 *                                          to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Book_Taxonomy[]|array Array of Book_Taxonomy objects.
 */
function get_book_taxonomies( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Book_Taxonomies_Query();

	return $query->query( $args );

}

/**
 * Count the taxonomies
 *
 * @param array $args
 *
 * @see get_book_taxonomies() for accepted arguments.
 *
 * @return int
 */
function count_book_taxonomies( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Book_Taxonomies_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new taxonomy
 *
 * @param array $args   {
 *
 * @type string $name   Taxonomy name.
 * @type string $slug   Unique taxonomy slug. Omit to auto generate.
 * @type string $diplay Display type - either `text` or `checkbox`.
 * }
 *
 * @return int ID of the newly created taxonomy.
 * @throws Exception
 */
function add_book_taxonomy( $args ) {

	$args = wp_parse_args( $args, array(
		'name'    => '',
		'slug'    => '',
		'display' => 'text'
	) );

	if ( empty( $args['name'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A taxonomy name is required.', 'book-database' ), 400 );
	}

	// Generate a slug.
	$args['slug'] = ! empty( $args['slug'] ) ? unique_book_slug( $args['slug'], 'book_taxonomy' ) : unique_book_slug( sanitize_title( $args['name'] ), 'book_taxonomy' );

	// Sanitize.
	$args['slug']    = sanitize_key( $args['slug'] );
	$args['display'] = sanitize_text_field( wp_strip_all_tags( $args['display'] ) );

	$query       = new Book_Taxonomies_Query();
	$taxonomy_id = $query->add_item( $args );

	if ( empty( $taxonomy_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new taxonomy into the database.', 'book-database' ), 500 );
	}

	return absint( $taxonomy_id );

}

/**
 * Update an existing taxonomy
 *
 * @param int   $taxonomy_id ID of the taxonomy to update.
 * @param array $args        Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_book_taxonomy( $taxonomy_id, $args = array() ) {

	// Slugs cannot be changed.
	if ( isset( $args['slug'] ) ) {
		throw new Exception( 'invalid_parameter', __( 'Taxonomy slugs cannot be changed.', 'book-database' ), 400 );
	}

	$query   = new Book_Taxonomies_Query();
	$updated = $query->update_item( $taxonomy_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the taxonomy.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a taxonomy
 *
 * @param int $taxonomy_id ID of the taxonomy to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_taxonomy( $taxonomy_id ) {

	// Cannot delete protected taxonomies.
	$taxonomy = get_book_taxonomy( $taxonomy_id );

	if ( empty( $taxonomy ) ) {
		return true;
	}

	$protected = array(
		'publisher',
		'genre',
		'source'
	);

	// You cannot delete these protected taxonomies.
	if ( in_array( $taxonomy->get_slug(), $protected ) ) {
		throw new Exception( 'protected_taxonomy', sprintf( __( 'The %s taxonomy cannot be deleted.', 'book-database' ), $taxonomy->get_slug() ), 400 );
	}

	$query   = new Book_Taxonomies_Query();
	$deleted = $query->delete_item( $taxonomy_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the taxonomy.', 'book-database' ), 500 );
	}

	return true;

}