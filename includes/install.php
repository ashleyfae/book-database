<?php
/**
 * Functions that run on install.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install
 *
 * Registers post types, custom taxonomies, and flushes
 * rewrite rules.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_install( $network_wide = false ) {
	global $wpdb;
	if ( is_multisite() && $network_wide ) {
		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			bdb_run_install();
			restore_current_blog();
		}
	} else {
		bdb_run_install();
	}
}

register_activation_hook( BDB_FILE, 'bdb_install' );

/**
 * Run Installation
 *
 * @since 1.0.0
 * @return void
 */
function bdb_run_install() {
	global $bdb_options;

	// Add Upgraded from Option
	$current_version = get_option( 'bdb_version' );
	if ( $current_version ) {
		update_option( 'bdb_version_upgraded_from', $current_version );
	}

	// Set up our default settings.
	/*$options         = array();
	$current_options = get_option( 'bdb_settings', array() );

	// Populate default values.
	foreach ( bdb_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {
			// Check for backwards compatibility
			$tab_sections = bdb_get_settings_tab_sections( $tab );
			if ( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section  = 'main';
				$settings = $sections;
			}
			foreach ( $settings as $option ) {
				if ( 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				} elseif ( 'book_layout' == $option['type'] && ! array_key_exists( 'book_layout', $current_options ) ) {
					$options[ $option['id'] ] = bdb_get_book_fields();
				}
			}
		}
	}

	$merged_options = array_merge( $bdb_options, $options );
	$bdb_options    = $merged_options;

	update_option( 'bdb_settings', $merged_options );*/
	update_option( 'bdb_version', BDB_VERSION );

	// Create the review database.
	@book_database()->reviews->create_table();
	@book_database()->review_meta->create_table();
	@book_database()->books->create_table();
	@book_database()->series->create_table();
	@book_database()->book_terms->create_table();
	@book_database()->book_term_relationships->create_table();
	@book_database()->reading_list->create_table();

	// Rewrite rules.
	bdb_rewrite_tags();
	bdb_rewrite_rules();
	flush_rewrite_rules();

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Add the transient to redirect.
	set_transient( '_bdb_activation_redirect', true, 30 );
}

/**
 * When a new Blog is created in multisite, see if Novelist is network activated, and run the installer.
 *
 * @param  int    $blog_id The Blog ID created
 * @param  int    $user_id The User ID set as the admin
 * @param  string $domain  The URL
 * @param  string $path    Site Path
 * @param  int    $site_id The Site ID
 * @param  array  $meta    Blog Meta
 *
 * @since 1.0.0
 * @return void
 */
function bdb_new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	if ( is_plugin_active_for_network( plugin_basename( BDB_FILE ) ) ) {
		switch_to_blog( $blog_id );
		bdb_install();
		restore_current_blog();
	}
}

add_action( 'wpmu_new_blog', 'bdb_new_blog_created', 10, 6 );