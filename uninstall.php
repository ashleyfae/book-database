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

// Drop tables.
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_book_term_relationships" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_book_terms" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_books" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_reading_log" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_reviewmeta" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_reviews" );
$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "bdb_series" );