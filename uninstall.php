<?php
/**
 * Uninstall Book Database
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load BDB
include_once( 'book-database.php' );

// Bail if delete isn't enabled.
if ( true !== bdb_get_option( 'delete_on_uninstall' ) ) {
	return;
}

// @todo