<?php
/**
 * Series Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/*
 * Note: there is no singular `get_book_series_by()` function because both the singular
 * and plural forms are the same, so that's confusing. `get_book_series_by()` is reserved
 * for the plural version. To get a single series by ID, use `get_book_series_by( 'id', $id )`
 */

/**
 * Get a single series by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Series|false
 */
function get_book_series_by( $column_name, $column_value ) {

	$query = new Series_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for series
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type string       $name                Filter by name. Default empty.
 * @type string       $slug                Filter by slug. Default empty.
 * @type int          $number_books        Filter by number of books in the series. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'name', 'slug', 'number_books', 'date_created', and
 *                                         'date_modified'. Also accepts false, an empty array, or 'none'
 *                                          to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Series[] Array of Series objects.
 */
function get_book_series( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Series_Query();

	return $query->query( $args );

}

/**
 * Count the series
 *
 * @param array $args
 *
 * @see get_book_series() for accepted arguments.
 *
 * @return int
 */
function count_book_series( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Series_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new series
 *
 * @param array $args         {
 *
 * @type string $name         Name of the series.
 * @type string $slug         Series slug. Omit to auto generate.
 * @type string $description  Description of the series.
 * @type int    $number_books Number of books planned for the series.
 * }
 *
 * @return int ID of the newly created taxonomy.
 * @throws Exception
 */
function add_book_series( $args ) {

	$args = wp_parse_args( $args, array(
		'name'         => '',
		'slug'         => '',
		'description'  => '',
		'number_books' => 1
	) );

	if ( empty( $args['name'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A taxonomy name is required.', 'book-database' ), 400 );
	}

	// Generate a slug.
	$args['slug'] = ! empty( $args['slug'] ) ? unique_book_slug( $args['slug'], 'series' ) : unique_book_slug( sanitize_title( $args['name'] ), 'series' );

	// Sanitize.
	$args['slug'] = sanitize_key( $args['slug'] );

	$query     = new Series_Query();
	$series_id = $query->add_item( $args );

	if ( empty( $series_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new series into the database.', 'book-database' ), 500 );
	}

	return absint( $series_id );

}

/**
 * Update an existing series
 *
 * @param int   $series_id ID of the series to update.
 * @param array $args      Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_book_series( $series_id, $args = array() ) {

	$series = get_book_series_by( 'id', $series_id );

	if ( empty( $series ) ) {
		throw new Exception( 'invalid_id', __( 'Invalid series ID.', 'book-database' ), 400 );
	}

	// If the slug is changing, let's re-generate it.
	if ( isset( $args['slug'] ) && $args['slug'] != $series->get_slug() ) {
		$args['slug'] = unique_book_slug( $args['slug'], 'series' );
	}

	$query   = new Series_Query();
	$updated = $query->update_item( $series_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the series.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a series
 *
 * This also updates the records of each book in this series to wipe their series_id and series_position.
 *
 * @param int $series_id ID of the book to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_series( $series_id ) {

	$query   = new Series_Query();
	$deleted = $query->delete_item( $series_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the series.', 'book-database' ), 500 );
	}

	// Get all the books in this series.
	$books = get_books( array(
		'series_id' => absint( $series_id ),
		'number'    => 9999,
		'fields'    => 'id'
	) );

	if ( $books ) {
		/**
		 * @var int[] $books
		 */
		// Remove series_id and series_position from each book.
		foreach ( $books as $book_id ) {
			update_book( $book_id, array(
				'series_id'       => 0,
				'series_position' => null
			) );
		}
	}

	return true;

}