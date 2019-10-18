<?php
/**
 * Admin Book Term Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the URL for deleting a term
 *
 * @param int $term_id ID of the term to delete.
 *
 * @return string
 */
function get_delete_book_term_url( $term_id ) {
	return wp_nonce_url( get_book_terms_admin_page_url( array(
		'bdb_action' => 'delete_term',
		'term_id'  => $term_id
	) ), 'bdb_delete_term' );
}