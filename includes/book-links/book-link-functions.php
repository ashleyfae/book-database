<?php
/**
 * Book Link Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single link by its ID
 *
 * @param int $link_id
 *
 * @return Book_Link|false
 */
function get_book_link( $link_id ) {

	$query = new Book_Links_Query();

	return $query->get_item( $link_id );

}

/**
 * Get a single link by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Book_Link|false
 */
function get_book_link_by( $column_name, $column_value ) {

	$query = new Book_Links_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for links
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type int          $book_id             Filter by book ID. Default empty.
 * @type int          $retailer_id         Filter by retailer ID. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'book_id', 'retailer_id', 'date_created', and 'date_modified'.
 *                                         Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
 *                                         Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Book_Link[] Array of Book_Link objects.
 */
function get_book_links( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Book_Links_Query();

	return $query->query( $args );

}

/**
 * Count the links
 *
 * @param array $args
 *
 * @see get_book_links() for accepted arguments.
 *
 * @return int
 */
function count_book_links( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Book_Links_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new link
 *
 * @param array $args        {
 *
 * @type int    $book_id     Required. ID of the book this link is for.
 * @type int    $retailer_id Required. ID of the retailer this link is for.
 * @type string $url         Required. URL for the link.
 * }
 *
 * @return int ID of the newly created retailer.
 * @throws Exception
 */
function add_book_link( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'book_id'     => 0,
		'retailer_id' => 0,
		'url'         => ''
	) );

	if ( empty( $args['book_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
	}
	if ( empty( $args['retailer_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A retailer ID is required.', 'book-database' ), 400 );
	}
	if ( empty( $args['url'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A url is required.', 'book-database' ), 400 );
	}

	$query   = new Book_Links_Query();
	$link_id = $query->add_item( $args );

	if ( empty( $link_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new book link into the database.', 'book-database' ), 500 );
	}

	return absint( $link_id );

}

/**
 * Update an existing book link
 *
 * @param int   $link_id ID of the link to update.
 * @param array $args    Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_book_link( $link_id, $args = array() ) {

	$query   = new Book_Links_Query();
	$updated = $query->update_item( $link_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the book link.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a book link
 *
 * @param int $link_id ID of the link to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_link( $link_id ) {

	$query   = new Book_Links_Query();
	$deleted = $query->delete_item( $link_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the link.', 'book-database' ), 500 );
	}

	return true;

}