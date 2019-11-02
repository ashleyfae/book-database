<?php
/**
 * Retailer Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single retailer by its ID
 *
 * @param int $retailer_id
 *
 * @return Retailer|false
 */
function get_retailer( $retailer_id ) {

	$query = new Retailers_Query();

	return $query->get_item( $retailer_id );

}

/**
 * Get a single retailer by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Retailer|false
 */
function get_retailer_by( $column_name, $column_value ) {

	$query = new Retailers_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for retailers
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type string       $name                Filter by name. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'name', 'date_created', and 'date_modified'. Also accepts
 *                                         false, an empty array, or 'none' to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Retailer[] Array of Retailer objects.
 */
function get_retailers( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Retailers_Query();

	return $query->query( $args );

}

/**
 * Count the retailers
 *
 * @param array $args
 *
 * @see get_retailers() for accepted arguments.
 *
 * @return int
 */
function count_retailers( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Retailers_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new retailer
 *
 * @param array $args     {
 *
 * @type string $name     Required. Name of the retailer.
 * @type string $template Optional. Template for use in book info.
 * }
 *
 * @return int ID of the newly created retailer.
 * @throws Exception
 */
function add_retailer( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'name'     => '',
		'template' => ''
	) );

	if ( empty( $args['name'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'An author name is required.', 'book-database' ), 400 );
	}

	$query       = new Retailers_Query();
	$retailer_id = $query->add_item( $args );

	if ( empty( $retailer_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new retailer into the database.', 'book-database' ), 500 );
	}

	return absint( $retailer_id );

}

/**
 * Update an existing retailer
 *
 * @param int   $retailer_id ID of the retailer to update.
 * @param array $args        Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_retailer( $retailer_id, $args = array() ) {

	$query   = new Retailers_Query();
	$updated = $query->update_item( $retailer_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the retailer.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a retailer
 *
 * @param int $retailer_id ID of the retailer to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_retailer( $retailer_id ) {

	global $wpdb;

	$query   = new Retailers_Query();
	$deleted = $query->delete_item( $retailer_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the retailer.', 'book-database' ), 500 );
	}

	$link_table = book_database()->get_table( 'book_links' )->get_table_name();

	// Delete all links for this retailer.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$link_table} WHERE retailer_id = %d", $retailer_id ) );

	return true;

}