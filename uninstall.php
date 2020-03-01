<?php
/**
 * Uninstall Book Database
 *
 * Delete the following data when the plugin is uninstalled:
 *      - All options.
 *      - Custom tables.
 *      - Custom capabilities.
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

namespace Book_Database;

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load BDB
include_once( 'book-database.php' );
book_database();

// Bail if delete isn't enabled.
if ( ! function_exists( __NAMESPACE__ . '\bdb_get_option' ) || ! bdb_get_option( 'delete_on_uninstall' ) ) {
	return;
}

global $wpdb;

// Remove all plugin settings.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'bdb\_%'" );

// Drop tables
foreach ( book_database()->get_tables() as $table ) {
	if ( $table->exists() ) {
		$table->uninstall();
	}
}

// Remove capabilities
$role = get_role( 'administrator' );

foreach ( get_book_capabilities() as $capability ) {
	$role->remove_cap( $capability );
}