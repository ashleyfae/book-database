<?php
/**
 * Owned Edition Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single edition by its ID
 *
 * @param int $edition_id
 *
 * @return Edition|false
 */
function get_edition( $edition_id ) {

	$query = new Editions_Query();

	return $query->get_item( $edition_id );

}

/**
 * Get a single edition by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Edition|false
 */
function get_edition_by( $column_name, $column_value ) {

	$query = new Editions_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for editions
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type int          $book_id             Filter by book ID. Default empty.
 * @type string       $isbn                Filter by ISBN. Default empty.
 * @type string       $format              Filter by format. Default empty.
 * @type array        $format__in          Array of formats to include. Default empty.
 * @type array        $format__not_in      Array of formats to exclude. Default empty.
 * @type array        $date_acquired_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type int          $source_id           Filter by source term ID. Default empty.
 * @type int          $signed              Filter by signed status. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'book_id', 'isbn', 'format', 'date_acquired', 'source_id',
 *                                         'signed', 'date_created', and 'date_modified'. Also accepts false, an empty
 *                                         array, or 'none' to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Edition[] Array of Edition objects.
 */
function get_editions( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Editions_Query();

	return $query->query( $args );

}

/**
 * Count the editions
 *
 * @param array $args
 *
 * @see get_editions() for accepted arguments.
 *
 * @return int
 */
function count_editions( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Editions_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new edition
 *
 * @param array      $args          {
 *
 * @type int         $book_id       Required. ID of the corresponding book.
 * @type string      $isbn          Optional. ISBN or ASIN.
 * @type string      $format        Optional. Format of the book (e.g. `hardcover`, `paperback`, etc.)
 * @type string|null $date_acquired Optional. Date you acquired the book, in UTC / MySQL format.
 * @type int|null    $source_id     Optional. ID of the `source` term.
 * @type int|null    $signed        Optional. `1` if the book is signed, `null` if not.
 * }
 *
 * @return int ID of the newly created edition.
 * @throws Exception
 */
function add_edition( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'book_id'       => 0,
		'isbn'          => '',
		'format'        => '',
		'date_acquired' => null,
		'source_id'     => null,
		'signed'        => null
	) );

	if ( empty( $args['book_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
	}

	$query      = new Editions_Query();
	$edition_id = $query->add_item( $args );

	if ( empty( $edition_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new edition into the database.', 'book-database' ), 500 );
	}

	return absint( $edition_id );

}

/**
 * Update an existing edition
 *
 * @param int   $edition_id ID of the edition to update.
 * @param array $args       Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_edition( $edition_id, $args = array() ) {

	$query   = new Editions_Query();
	$updated = $query->update_item( $edition_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the edition.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete an edition
 *
 * @param int $edition_id ID of the edition to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_edition( $edition_id ) {

	$query   = new Editions_Query();
	$deleted = $query->delete_item( $edition_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the edition.', 'book-database' ), 500 );
	}

	// Find all reading logs with this edition ID and change their value to `null`.
	$logs = get_reading_logs( array(
		'edition_id' => $edition_id
	) );
	if ( $logs ) {
		foreach ( $logs as $log ) {
			update_reading_log( $log->get_id(), array(
				'edition_id' => null
			) );
		}
	}

	return true;

}

/**
 * Get an array of all available book formats
 *
 * @return array
 */
function get_book_formats() {

	$formats = array(
		'arc'       => __( 'ARC', 'book-database' ),
		'audiobook' => __( 'Audiobook', 'book-database' ),
		'earc'      => __( 'eARC', 'book-database' ),
		'ebook'     => __( 'eBook', 'book-database' ),
		'hardcover' => __( 'Hardcover', 'book-database' ),
		'paperback' => __( 'Paperback', 'book-database' ),
	);

	/**
	 * Filters the formats
	 *
	 * @param array $formats
	 */
	return apply_filters( 'book-database/book/formats', $formats );

}