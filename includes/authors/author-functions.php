<?php
/**
 * Author Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Database\Authors\AuthorsQuery;
use Book_Database\Exceptions\Exception;
use Book_Database\Models\Author;

/**
 * Get a single author by its ID
 *
 * @param int $author_id
 *
 * @return Author|false
 */
function get_book_author( $author_id ) {

	$query = new AuthorsQuery();

	return $query->get_item( $author_id );

}

/**
 * Get a single author by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Author|false
 */
function get_book_author_by( $column_name, $column_value ) {

	$query = new AuthorsQuery();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for authors
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
 * @type int          $image_id            Filter by image ID. Default empty.
 * @type int          $book_count          Filter by book count. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'name', 'slug', 'image_id', 'book_count',
 *                                         'date_created', and 'date_modified'. Also accepts false, an empty array,
 *                                         or 'none' to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Author[] Array of Author objects.
 */
function get_book_authors( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new AuthorsQuery();

	return $query->query( $args );

}

/**
 * Count the authors
 *
 * @param array $args
 *
 * @see get_book_authors() for accepted arguments.
 *
 * @return int
 */
function count_book_authors( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new AuthorsQuery( $args );

	return absint( $query->found_items );

}

/**
 * Add a new author
 *
 * @param array       $args        {
 *
 * @type string       $name        Required. Full name of the author.
 * @type string       $slug        Optional. Unique URL-friendly author slug. Auto generated if omitted.
 * @type string       $description Optional. Author description.
 * @type int|null     $image_id    Optional. ID of the image attachment (author photo).
 * @type string|array $links       Optional. Array of links to author websites.
 * @type int          $book_count  Optional. Number of books in the database by this author.
 * }
 *
 * @return int ID of the newly created author.
 * @throws Exception
 */
function add_book_author( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'name'        => '',
		'slug'        => '',
		'description' => '',
		'image_id'    => null,
		'links'       => '',
		'book_count'  => 0
	) );

	if ( empty( $args['name'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'An author name is required.', 'book-database' ), 400 );
	}

	// Generate a slug.
	$args['slug'] = ! empty( $args['slug'] ) ? unique_book_slug( $args['slug'], 'book_taxonomy' ) : unique_book_slug( sanitize_title( $args['name'] ), 'author' );

	$query     = new AuthorsQuery();
	$author_id = $query->add_item( $args );

	if ( empty( $author_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new author into the database.', 'book-database' ), 500 );
	}

	return absint( $author_id );

}

/**
 * Update an existing author
 *
 * @param int   $author_id ID of the author to update.
 * @param array $args      Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_book_author( $author_id, $args = array() ) {

	$author = get_book_author( $author_id );

	if ( empty( $author ) ) {
		throw new Exception( 'invalid_id', __( 'Invalid author ID.', 'book-database' ), 400 );
	}

	// If the slug is changing, let's re-generate it.
	if ( isset( $args['slug'] ) && $args['slug'] != $author->get_slug() ) {
		$args['slug'] = unique_book_slug( $args['slug'], 'author' );
	}

	$query   = new AuthorsQuery();
	$updated = $query->update_item( $author_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the author.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete an author
 *
 * @param int $author_id ID of the author to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_author( $author_id ) {

	$query   = new AuthorsQuery();
	$deleted = $query->delete_item( $author_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the author.', 'book-database' ), 500 );
	}

	/**
	 * @var int[] $relationships
	 */
	$relationships = get_book_author_relationships( array(
		'author_id' => $author_id,
		'fields'    => 'id'
	) );

	if ( $relationships ) {
		foreach ( $relationships as $relationship_id ) {
			delete_book_author_relationship( $relationship_id );
		}
	}

	return true;

}

/**
 * Recalculate and update the author's book count
 *
 * @param int $author_id ID of the author to update the count of.
 *
 * @return bool True on success, false on failure.
 */
function recalculate_author_book_count( $author_id ) {

	$new_count = count_book_author_relationships( array(
		'author_id' => $author_id
	) );

	try {
		update_book_author( $author_id, array(
			'book_count' => absint( $new_count )
		) );

		return true;
	} catch ( Exception $e ) {
		return false;
	}

}

/**
 * Get the authors admin page URL.
 *
 * @param array $args Query args to append to the URL.
 *
 * @return string
 */
function get_authors_admin_page_url( $args = array() ) {

	$sanitized_args = array();

	foreach ( $args as $key => $value ) {
		$sanitized_args[ sanitize_key( $key ) ] = urlencode( $value );
	}

	return add_query_arg( $sanitized_args, admin_url( 'admin.php?page=bdb-authors' ) );

}
