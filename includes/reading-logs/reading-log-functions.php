<?php
/**
 * Reading Log Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single reading log entry by its ID
 *
 * @param int $log_id
 *
 * @return Reading_Log|false
 */
function get_reading_log( $log_id ) {

	$query = new Reading_Logs_Query();

	return $query->get_item( $log_id );

}

/**
 * Get a single reading log entry by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Reading_Log|false
 */
function get_reading_log_by( $column_name, $column_value ) {

	$query = new Reading_Logs_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for reading logs
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type int          $book_id             Filter by book ID. Default empty.
 * @type array        $book_id__in         An array of book IDs to include. Default empty.
 * @type int          $review_id           Filter by review ID. Default empty.
 * @type int          $user_id             Filter by user ID. Default empty.
 * @type array        $user_id__in         An array of user IDs to include. Default empty.
 * @type array        $user_id__not_in     An array of user IDs to exclude. Default empty.
 * @type array        $date_started_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_finished_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type float        $percentage_complete Filter by percentage complete. Default empty.
 * @type float        $rating              Filter by rating.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'book_id', 'review_id', 'user_id', 'date_started',
 *                                         'date_finished', 'percentage_complete', 'rating', and 'date_modified'.
 *                                         Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
 *                                         Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Reading_Log[] Array of Reading_Log objects.
 */
function get_reading_logs( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Reading_Logs_Query();

	return $query->query( $args );

}

/**
 * Count the reading logs
 *
 * @param array $args
 *
 * @see get_reading_logs() for accepted arguments.
 *
 * @return int
 */
function count_reading_logs( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Reading_Logs_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new reading log
 *
 * @param array      $args                {
 *
 * @type int         $book_id             Required. ID of the book that was read.
 * @type int         $review_id           Optional. ID of the review associated with this log.
 * @type int         $user_id             Optional. ID of the user who read the log. Defaults to current user.
 * @type string|null $date_started        Optional. Date the book was started in UTC / MySQL format.
 * @type string|null $date_finished       Optional. Date the book was finished in UTC / MySQL format.
 * @type float       $percentage_complete Optional. Percentage of the book completed.
 * @type float|null  $rating              Optional. Rating.
 * }
 *
 * @return int ID of the newly created log.
 * @throws Exception
 */
function add_reading_log( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'book_id'             => 0,
		'review_id'           => 0,
		'user_id'             => get_current_user_id(),
		'date_started'        => null,
		'date_finished'       => null,
		'percentage_complete' => 0,
		'rating'              => null
	) );

	if ( empty( $args['book_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
	}

	if ( empty( $args['user_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A user ID is required.', 'book-database' ), 400 );
	}

	$query  = new Reading_Logs_Query();
	$log_id = $query->add_item( $args );

	if ( empty( $log_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new log into the database.', 'book-database' ), 500 );
	}

	return absint( $log_id );

}

/**
 * Update an existing reading log
 *
 * @param int   $log_id ID of the log to update.
 * @param array $args   Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_reading_log( $log_id, $args = array() ) {

	$query   = new Reading_Logs_Query();
	$updated = $query->update_item( $log_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the reading log.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a reading log
 *
 * @param int $log_id ID of the log to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_reading_log( $log_id ) {

	$query   = new Reading_Logs_Query();
	$deleted = $query->delete_item( $log_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the reading log.', 'book-database' ), 500 );
	}

	return true;

}