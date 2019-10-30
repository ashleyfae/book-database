<?php
/**
 * Admin Series Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the URL for deleting a series
 *
 * @param int $series_id ID of the series to delete.
 *
 * @return string
 */
function get_delete_series_url( $series_id ) {
	return wp_nonce_url( get_series_admin_page_url( array(
		'bdb_action' => 'delete_series',
		'series_id'  => $series_id
	) ), 'bdb_delete_series' );
}