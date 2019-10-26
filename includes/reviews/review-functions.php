<?php
/**
 * Review Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single review by its ID
 *
 * @param int $review_id
 *
 * @return Review|false
 */
function get_review( $review_id ) {

	$query = new Reviews_Query();

	return $query->get_item( $review_id );

}

/**
 * Get a single review by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Review|false
 */
function get_review_by( $column_name, $column_value ) {

	$query = new Reviews_Query();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for reviews
 *
 * @param array       $args                 {
 *                                          Query arguments to override the defaults.
 *
 * @type int          $id                   An item ID to only return that item. Default empty.
 * @type array        $id__in               An array of item IDs to include. Default empty.
 * @type array        $id__not_in           An array of item IDs to exclude. Default empty.
 * @type int          $book_id              Filter by book ID. Default empty.
 * @type array        $book_id__in          An array of book IDs to include. Default empty.
 * @type array        $book_id__not_in      An array of book IDs to exclude. Default empty.
 * @type array        $post_id              Filter by post ID. Default empty.
 * @type array        $post_id__in          An array of post IDs to include. Default empty.
 * @type int          $user_id              Filter by user ID. Default empty.
 * @type array        $user_id__in          An array of user IDs to include. Default empty.
 * @type array        $user_id__not_in      An array of user IDs to exclude. Default empty.
 * @type array        $date_written_query   Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_published_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_created_query   Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query           Query all datetime columns together. See WP_Date_Query.
 * @type array        $reading_log_query    Query for series. See \Book_Database\BerlinDB\Database\Queries\Reading_Log.
 * @type array        $tax_query            Query for taxonomy terms. See WP_Tax_Query.
 * @type bool         $count                Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields               Item fields to return. Accepts any column known names  or empty
 *                                          (returns an array of complete item objects). Default empty.
 * @type int          $number               Limit number of items to retrieve. Default 20.
 * @type int          $offset               Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows        Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby              Accepts 'id', 'book_id', 'post_id', 'user_id', 'date_written',
 *                                          'date_published', 'date_created', and 'date_modified'. Also accepts false,
 *                                          an empty array, or 'none' to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order                How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search               Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache         Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Review[] Array of Review objects.
 */
function get_reviews( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Reviews_Query();

	return $query->query( $args );

}

/**
 * Count the reviews
 *
 * @param array $args
 *
 * @see get_reviews() for accepted arguments.
 *
 * @return int
 */
function count_reviews( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Reviews_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new review
 *
 * @param array      $args           {
 *
 * @type int         $book_id        Required. ID of the book this review is of.
 * @type int|null    $post_id        Optional. ID of the post where the review is written.
 * @type int         $user_id        Optional. ID of the user who wrote the review. Default to current user ID.
 * @type string      $url            Optional. External URL for where the review is published.
 * @type string      $review         Optional. Review contents.
 * @type string      $date_written   Optional. Date the review was written in MySQL format / UTC. Default to now.
 * @type string|null $date_published Optional. Date the review was (or will be) published in MySQL format / UTC>
 * }
 *
 * @return int ID of the newly created review.
 * @throws Exception
 */
function add_review( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'book_id'        => 0,
		'post_id'        => null,
		'user_id'        => get_current_user_id(),
		'url'            => '',
		'review'         => '',
		'date_written'   => current_time( 'mysql', true ),
		'date_published' => null
	) );

	if ( empty( $args['book_id'] ) ) {
		throw new Exception( 'missing_parameter', __( 'Book ID is required.', 'book-database' ), 400 );
	}

	$query     = new Reviews_Query();
	$review_id = $query->add_item( $args );

	if ( empty( $review_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new review into the database.', 'book-database' ), 500 );
	}

	return absint( $review_id );

}

/**
 * Update an existing review
 *
 * @param int   $review_id ID of the review to update.
 * @param array $args      Arguments to update.
 *
 * @return bool
 * @throws Exception
 */
function update_review( $review_id, $args = array() ) {

	$query   = new Reviews_Query();
	$updated = $query->update_item( $review_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the review.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a review
 *
 * @param int $review_id ID of the review to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_review( $review_id ) {

	$query   = new Reviews_Query();
	$deleted = $query->delete_item( $review_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the review.', 'book-database' ), 500 );
	}

	// Find all logs associated with this review and wipe it.
	$reading_logs = get_reading_logs( array(
		'review_id' => $review_id
	) );

	if ( ! empty( $reading_logs ) ) {
		foreach ( $reading_logs as $reading_log ) {
			update_reading_log( $reading_log->get_id(), array(
				'review_id' => null
			) );
		}
	}

	// @todo delete all review meta

	return true;

}

/**
 * Get the reviews admin page URL.
 *
 * @param array $args Query args to append to the URL.
 *
 * @return string
 */
function get_reviews_admin_page_url( $args = array() ) {

	$sanitized_args = array();

	foreach ( $args as $key => $value ) {
		$sanitized_args[ sanitize_key( $key ) ] = urlencode( $value );
	}

	return add_query_arg( $sanitized_args, admin_url( 'admin.php?page=bdb-reviews' ) );

}

/**
 * Returns an array of distinct user IDs.
 *
 * @return array
 */
function get_reviewer_user_ids() {

	global $wpdb;

	$review_table = book_database()->get_table( 'reviews' )->get_table_name();

	$results = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$review_table}" );

	return $results;

}

/**
 * Returns an array of all the years that reviews have been written/published in.
 *
 * @param string $type  Date type - either `written` or `published`.
 * @param string $order Either ASC or DESC.
 *
 * @return array
 */
function get_review_years( $type = 'written', $order = 'DESC' ) {

	global $wpdb;

	$review_table = book_database()->get_table( 'reviews' )->get_table_name();
	$date_type    = 'written' === $type ? 'date_written' : 'date_published';
	$order        = 'DESC' === $order ? 'DESC' : 'ASC';
	$years        = $wpdb->get_col( "SELECT DISTINCT YEAR( {$date_type} ) FROM {$review_table} WHERE {$date_type} IS NOT NULL ORDER BY {$date_type} {$order}" );

	return $years;

}