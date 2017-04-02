<?php
/**
 * Template Functions
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
 * Get Templates Directory
 *
 * Returns the path to the BDB templates directory.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_templates_dir() {
	return BDB_DIR . 'templates';
}

/**
 * Get Templates URL
 *
 * Returns the URL to the BDB templates directory.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_templates_url() {
	return BDB_URL . 'templates';
}

/**
 * Get Template Part
 *
 * Returns or includes a template part.
 *
 * Taken from bbPress.
 *
 * @param string $slug Template slug.
 * @param string $name Template name (after the dash). Optional.
 * @param bool   $load Whether or not to call `load_template()` to include the file.
 *
 * @uses  bdb_locate_template()
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_template_part( $slug, $name = '', $load = true ) {

	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts.
	$templates = array();

	if ( ! empty( $name ) ) {
		$templates[] = $slug . '-' . $name . '.php';
	}

	$templates[] = $slug . '.php';

	// Allow template parts to be filtered.
	$templates = apply_filters( 'book-database/get-template-part', $templates, $slug, $name );

	return bdb_locate_template( $templates, $load );

}

/**
 * Locate Template
 *
 * Retrieve the name of the highest priority template file that exists.
 * Search waterfall:
 *
 *      + child-theme/book-dastabase/template.php
 *      + theme/book-database/template.php
 *      + book-database/templates/template.php
 *
 * Taken from bbPress.
 *
 * @param array|string $template_names Array of possible template names to search for, in order.
 * @param bool         $load           Whether or not to load the template file.
 * @param bool         $require_once   Whether or not to use `require_once`.
 *
 * @uses  bdb_get_theme_template_paths()
 *
 * @since 1.0.0
 * @return string|false The template filename, or false if none is found.
 */
function bdb_locate_template( $template_names, $load = false, $require_once = false ) {

	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) ) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach ( bdb_get_theme_template_paths() as $template_path ) {

			if ( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if ( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;

}

/**
 * Get Theme Template Paths
 *
 * Returns an array of template paths that should be searched in to
 * look for a template part.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_theme_template_paths() {

	$template_dir = bdb_get_theme_template_dir_name();

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10  => trailingslashit( get_template_directory() ) . $template_dir,
		100 => bdb_get_templates_dir()
	);

	$file_paths = apply_filters( 'book-database/template-paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );

}

/**
 * Get Theme Template Directory Name
 *
 * This is the name of the directory you should put in your theme to
 * override BDB templates.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'book-database/templates-dir', 'book-database' ) );
}