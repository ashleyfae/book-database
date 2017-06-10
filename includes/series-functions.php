<?php
/**
 * Series Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete a Series
 *
 * This deletes the actual series entry and also looks for books that are
 * in this series and removes the series ID from their entry.
 *
 * @param int $series_id
 *
 * @since 1.0
 * @return void
 */
function bdb_delete_series( $series_id ) {

	// Delete the series.
	book_database()->series->delete( $series_id );

	// Get all books in this series.
	$books = bdb_get_books( array(
		'series_id' => absint( $series_id )
	) );

	// Bail early if no results.
	if ( empty( $books ) || ! is_array( $books ) ) {
		return;
	}

	// Remove series_id and series_position from each book.
	foreach ( $books as $book ) {
		book_database()->books->update( $book->ID, array(
			'series_id'       => null,
			'series_position' => null
		) );
	}

}