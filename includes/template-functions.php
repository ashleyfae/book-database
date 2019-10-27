<?php
/**
 * Template Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the path to the Book Database templates directory
 *
 * @return string
 */
function get_book_templates_directory() {
	return BDB_DIR . 'templates';
}

/**
 * Returns or includes a template part
 *
 * @param string $slug Template slug.
 * @param string $name Template name (after the dash). Optional.
 * @param bool   $load Whether or not to call `load_template()` to include the file.
 *
 * @return string
 */
function get_book_template_part( $slug, $name = '', $load = true ) {

	$templates = array();

	if ( ! empty( $name ) ) {
		$templates[] = sprintf( '%s-%s.php', $slug, $name );
	}

	$templates[] = sprintf( '%s.php', $slug );

	/**
	 * Filters the template parts.
	 *
	 * @param array  $templates
	 * @param string $slug
	 * @param string $name
	 */
	$templates = apply_filters( 'book-database/get-template-part', $templates, $slug, $name );

	return locate_book_template( $templates, $load );

}

/**
 * Locate a template
 *
 * Retrieve the name of the highest priority template file that exists.
 * Search waterfall:
 *
 *      - child-theme/book-datase/template.php
 *      - theme/book-database/template.php
 *      - book-database/templates/template.php
 *
 * @param array $template_names Array of possible template names to search for, ordered by priority.
 * @param bool  $load           Whether or not to load the template file.
 * @param bool  $require_once   Whether or not to use `require_once`.
 *
 * @return string|false The template filename on success, false if none was located.
 */
function locate_book_template( $template_names, $load = false, $require_once = false ) {

	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		if ( empty( $template_name ) ) {
			continue;
		}

		$template_name = ltrim( $template_name, '/' );

		// Try to locate this template.
		foreach ( get_book_template_paths() as $template_path ) {
			if ( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break 2;
			}
		}

	}

	if ( $load && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;

}

/**
 * Get the template paths
 *
 * An array of valid template paths to locate templates in.
 *
 * @return array
 */
function get_book_template_paths() {

	$file_paths = array(
		1   => trailingslashit( get_stylesheet_directory() ) . 'book-database',
		10  => trailingslashit( get_template_directory() ) . 'book-database',
		100 => get_book_templates_directory()
	);

	/**
	 * Filters the available template paths.
	 *
	 * @param array $file_paths
	 */
	$file_paths = apply_filters( 'book-database/template-paths', $file_paths );

	// Sort the file paths by priority.
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );

}