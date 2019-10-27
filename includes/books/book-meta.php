<?php
/**
 * Book Meta
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get book meta
 *
 * @param int    $book_id ID of the book.
 * @param string $key     The meta key to retrieve the value of.
 * @param bool   $single  True to return a single result, false to return an array of results.
 *
 * @return mixed
 */
function get_book_meta( $book_id, $key = '', $single = true ) {
	return get_metadata( 'bdb_book', $book_id, $key, $single );
}

/**
 * Add book meta
 *
 * @param int    $book_id
 * @param string $meta_key
 * @param mixed  $meta_value
 * @param bool   $unique
 *
 * @return int|false ID of the meta on success, false on failure.
 */
function add_book_meta( $book_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'bdb_book', $book_id, $meta_key, $meta_value, $unique );
}

/**
 * Update book meta
 *
 * @param int    $book_id
 * @param string $meta_key
 * @param mixed  $meta_value
 * @param string $prev_value
 *
 * @return int|bool Meta ID if this was new meta, true if an existing value was updated, false on failure.
 */
function update_book_meta( $book_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'bdb_book', $book_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Delete meta from a book
 *
 * @param int    $book_id
 * @param string $meta_key
 * @param mixed  $meta_value
 *
 * @return bool
 */
function delete_book_meta( $book_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'bdb_book', $book_id, $meta_key, $meta_value );
}