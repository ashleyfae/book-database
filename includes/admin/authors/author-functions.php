<?php
/**
 * Admin Author Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the URL for deleting an author
 *
 * @param int $author_id ID of the author to delete.
 *
 * @return string
 */
function get_delete_author_url( $author_id ) {
	return wp_nonce_url( get_authors_admin_page_url( array(
		'bdb_action' => 'delete_author',
		'author_id'  => $author_id
	) ), 'bdb_delete_author' );
}