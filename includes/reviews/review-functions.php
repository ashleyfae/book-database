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
 * @param array $args                       {
 *                                          Query arguments to override the defaults.
 *
 * @type array  $author_query               Filter based on author fields/values.
 * @type array  $book_query                 Filter based on book fields/values.
 * @type array  $series_query               Filter based on series fields/values.
 * @type array  $reading_log_query          Filter based on reading log fields/values.
 * @type array  $review_query               Filter based on review fields/values.
 * @type array  $edition_query              Filter based on edition fields/values.
 * @type array  $tax_query                  Filter based on taxonomy fields/values.
 * @type string $orderby                    Field to order by. Must contain table alias prefix. Default `review.id`.
 * @type string $order                      How to order the results.
 * @type int    $number                     Number of results.
 * @type int    $offset                     Offset the results.
 * @type bool   $count                      Whether or not to only return a count. Default false.
 * }
 *
 * @return object[] Array of database objects.
 */
function get_reviews( $args = array() ) {

	$query = new Reviews_Query();

	return $query->get_reviews( $args );

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
		'reading_log_id' => null,
		'user_id'        => get_current_user_id(),
		'post_id'        => null,
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

	// Delete all review meta.
	global $wpdb;
	$tbl_meta = book_database()->get_table( 'review_meta' )->get_table_name();
	$query    = $wpdb->prepare( "DELETE FROM {$tbl_meta} WHERE bdb_review_id = %d", $review_id );
	$wpdb->query( $query );

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

/**
 * Returns an array of post types that you can add reviews to
 *
 * @return array
 */
function get_review_post_types() {
	$post_types = array( 'post' );

	return apply_filters( 'book-database/review-post-types', $post_types );
}