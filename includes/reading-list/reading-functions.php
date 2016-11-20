<?php
/**
 * Reading List Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Book Reading List
 *
 * Returns the reading entries for a given book.
 *
 * @param int $book_id
 *
 * @since 1.1.0
 * @return array|false
 */
function bdb_get_book_reading_list( $book_id ) {

	$entries = book_database()->reading_list->get_entries( array(
		'book_id' => absint( $book_id )
	) );

	return $entries;

}

/**
 * Insert or Update Reading Entry
 *
 * @param array $data Entry data. Arguments include:
 *                    `ID` - To update an existing entry (optional).
 *                    `book_id` - ID of the book that was read (required).
 *                    `review_id` - ID of the associated review.
 *                    `user_id` - ID of the user who read the book. If omitted, current user ID is used.
 *                    `date_started` - Date the book was started.
 *                    `date_finished` - Date the book was finished.
 *
 * @since 1.1.0
 * @return bool|false Entry ID on success or false on failure.
 */
function bdb_insert_reading_entry( $data = array() ) {

	// Book ID is required.
	if ( ! array_key_exists( 'book_id', $data ) ) {
		return false;
	}

	// Verify that the book exists.
	if ( ! book_database()->books->exists( $data['book_id'] ) ) {
		return false;
	}

	$sanitized_data = array();

	// Entry ID
	if ( array_key_exists( 'ID', $data ) ) {
		$sanitized_data['ID'] = absint( $data['ID'] );
	}

	// Book ID and review ID.
	$sanitized_data['book_id']   = absint( $data['book_id'] );
	$sanitized_data['review_id'] = array_key_exists( 'review_id', $data ) ? absint( $data['review_id'] ) : 0;

	// User ID
	if ( array_key_exists( 'user_id', $data ) ) {
		$sanitized_data['user_id'] = absint( $data['user_id'] );
	} else {
		$current_user              = wp_get_current_user();
		$sanitized_data['user_id'] = absint( $current_user->ID );
	}

	// Format start date.
	if ( array_key_exists( 'date_started', $data ) ) {
		$timestamp                      = strtotime( wp_strip_all_tags( $data['date_started'] ) );
		$sanitized_data['date_started'] = date( 'Y-m-d H:i:s', $timestamp );
	}
	// Format end date.
	if ( array_key_exists( 'date_finished', $data ) ) {
		$timestamp                       = strtotime( wp_strip_all_tags( $data['date_finished'] ) );
		$sanitized_data['date_finished'] = date( 'Y-m-d H:i:s', $timestamp );
	}

	$result = book_database()->reading_list->add( $sanitized_data );

	return $result;

}