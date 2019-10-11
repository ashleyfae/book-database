<?php
/**
 * Admin Book Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the URL for deleting a book
 *
 * @param int $book_id ID of the book to delete.
 *
 * @return string
 */
function get_delete_book_url( $book_id ) {
	return wp_nonce_url( get_books_admin_page_url( array(
		'bdb_action' => 'delete_book',
		'book_id'    => $book_id
	) ), 'bdb_delete_book' );
}