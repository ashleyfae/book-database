<?php
/**
 * Uninstall Book Database
 *
 * Delete the following data when the plugin is uninstalled:
 *      - All options.
 *      - Drop custom tables.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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

// Bail if delete isn't enabled.
if ( ! bdb_get_option( 'delete_on_uninstall' ) ) {
	return;
}

global $wpdb;

// Remove all plugin settings.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'bdb\_%'" );

// Drop tables
$tables = array(
	'authors',
	'book_author_relationships',
	'book_taxonomies',
	'book_term_relationships',
	'book_terms',
	'books',
	'book_meta',
	'owned_editions',
	'reading_log',
	'reviewmeta',
	'reviews',
	'review_meta',
	'series'
);

foreach ( $tables as $table_key ) {
	$table = book_database()->get_table( $table_key );

	if ( ! $table ) {
		continue;
	}

	if ( $table->exists() ) {
		$table->uninstall();
	}
}

// Remove capabilities
$role = get_role( 'administrator' );

foreach ( get_book_capabilities() as $capability ) {
	$role->remove_cap( $capability );
}