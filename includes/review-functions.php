<?php
/**
 * Review Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Review by ID
 *
 * @param int $review_id
 *
 * @since 1.0.0
 * @return BDB_Review|false
 */
function bdb_get_review( $review_id ) {
	$review = new BDB_Review( absint( $review_id ) );

	if ( ! $review->ID ) {
		return false;
	}

	return $review;
}

/**
 * Get reviews for a given post.
 *
 * @param int $post_id
 *
 * @since 1.0.0
 * @return array|false Array of review data.
 */
function bdb_get_post_reviews( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID(); // Use current ID if not specified.

	if ( ! $post_id ) {
		return false;
	}

	$reviews = book_database()->reviews->get_reviews( array( 'post_id' => $post_id ) );

	return $reviews;
}

/**
 * Get reviews for a given book.
 *
 * @param int $post_id
 *
 * @since 1.0.0
 * @return array|false Array of review data.
 */
function bdb_get_book_reviews( $book_id, $args = array() ) {
	$defaults = array( 'book_id' => absint( $book_id ) );
	$args     = wp_parse_args( $args, $defaults );

	$reviews = book_database()->reviews->get_reviews( $args );

	return $reviews;
}

/**
 * Get Total Number of Reviews
 *
 * @param array $args Array of arguments for the query.
 *
 * @since 1.0.0
 * @return int
 */
function bdb_count_reviews( $args ) {
	return book_database()->reviews->count( $args );
}

/**
 * Insert New Review
 *
 * If the `ID` key is passed into the `$data` array then an existing review
 * is updated instead.
 *
 * @param array $data   Review data. Arguments include:
 *                      `ID` - To update an existing book (optional).
 *                      `book_id` - Book ID associated with the review (optional).
 *                      `post_id` - Post associated with the review (optional).
 *                      `url` - URL to an external book review (optional).
 *                      `user_id` - User who the review is for. If omitted, current user is used.
 *                      `rating` - Rating for the book (optional).
 *                      `date_added` - Timestamp for when the review was added. If omitted, current time is used.
 *
 * @since 1.0.0
 * @return int|WP_Error ID of the review inserted or updated, or WP_Error on failure.
 */
function bdb_insert_review( $data = array() ) {

	$review_db_data = array();

	// Book ID
	if ( array_key_exists( 'book_id', $data ) && $data['book_id'] > 0 ) {
		$review_db_data['book_id'] = absint( $data['book_id'] );
	}

	// Post ID
	if ( array_key_exists( 'post_id', $data ) && $data['post_id'] > 0 ) {
		$review_db_data['post_id'] = absint( $data['post_id'] );
	}

	// URL
	if ( array_key_exists( 'url', $data ) ) {
		$review_db_data['url'] = esc_url_raw( $data['url'] );
	}

	// User ID
	if ( array_key_exists( 'user_id', $data ) && $data['user_id'] >= 0 ) {
		$review_db_data['user_id'] = absint( $data['user_id'] );
	} else {
		$current_user              = wp_get_current_user();
		$review_db_data['user_id'] = absint( $current_user->ID );
	}

	// Rating
	if ( array_key_exists( 'rating', $data ) ) {
		$allowed_ratings = bdb_get_available_ratings();

		if ( array_key_exists( $data['rating'], $allowed_ratings ) ) {
			$review_db_data['rating'] = sanitize_text_field( $data['rating'] );
		}
	}

	// Date Added
	if ( array_key_exists( 'date_added', $data ) ) {
		$review_db_data['date_added'] = sanitize_text_field( $data['date_added'] );
	}

	// Review ID
	if ( array_key_exists( 'ID', $data ) && $data['ID'] > 0 ) {
		$review_db_data['ID'] = absint( $data['ID'] );
	}

	$review_id = book_database()->reviews->add( $review_db_data );

	if ( ! $review_id ) {
		return new WP_Error( 'error-inserting-review', __( 'Error inserting review into database.', 'book-database' ) );
	}

	return $review_id;

}

/**
 * Get Review Years
 *
 * Returns an array of all the years that reviews have been published in.
 *
 * @param string $order Either ASC or DESC.
 *
 * @since 1.0.0
 * @return array|false
 */
function bdb_get_review_years( $order = 'DESC' ) {
	global $wpdb;
	$reviews_table = book_database()->reviews->table_name;
	$years         = $wpdb->get_col( "SELECT DISTINCT YEAR(date_added) FROM $reviews_table" );

	if ( ! is_array( $years ) ) {
		return false;
	}

	if ( 'DESC' == $order ) {
		arsort( $years );
	} else {
		asort( $years );
	}

	$final_years = array();

	foreach ( $years as $year ) {
		$final_years[ absint( $year ) ] = absint( $year );
	}

	return $final_years;
}