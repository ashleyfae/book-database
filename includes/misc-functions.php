<?php
/**
 * Misc. Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get an array of all settings
 *
 * @return array
 */
function bdb_get_settings() {
	return get_option( 'bdb_settings', array() );
}

/**
 * Get an option value
 *
 * @param string $key
 * @param bool   $default
 *
 * @return mixed
 */
function bdb_get_option( $key, $default = false ) {

	$settings = bdb_get_settings();

	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;

}

/**
 * Update an option
 *
 * @param string $key
 * @param mixed  $value
 *
 * @return bool
 */
function bdb_update_option( $key, $value ) {

	$settings = bdb_get_settings();

	$settings[ $key ] = $value;

	return update_option( 'bdb_settings', $settings );

}

/**
 * Format a date for display
 *
 * This converts a GMT date to local site time and formats it.
 *
 * @param string $date   Date string to format.
 * @param string $format Date format. Default is the site's `date_format`.
 *
 * @return string
 */
function format_date( $date, $format = '' ) {

	$format     = ! empty( $format ) ? $format : get_option( 'date_format' );
	$local_date = get_date_from_gmt( $date, 'U' );

	return date_i18n( $format, $local_date );

}

/**
 * Generate a unique book term slug
 *
 * Checks to see if the given slug already exists. If so, numbers are appended
 * until the slug becomes available.
 *
 * @see wp_unique_post_slug()
 *
 * @param string $slug     Desired slug.
 * @param string $taxonomy Accepts any taxonomy slug or `series`.
 *
 * @return string A unique slug.
 */
function unique_book_term_slug( $slug, $taxonomy = 'author' ) {

	// Check if this slug already exists.
	if ( 'series' === $taxonomy ) {
		$terms = get_series_by( 'slug', $slug );
	} else {
		$terms = count_book_terms( array(
			'taxonomy' => $taxonomy,
			'slug'     => $slug
		) );
	}

	$new_slug = $slug;

	if ( $terms ) {
		$suffix = 2;

		do {

			$alt_slug = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . '-' . $suffix;

			if ( 'series' === $taxonomy ) {
				$terms = get_series_by( 'slug', $alt_slug );
			} else {
				$terms = count_book_terms( array(
					'taxonomy' => $taxonomy,
					'slug'     => $alt_slug
				) );
			}

			$suffix ++;

		} while ( $terms );
	}

	return apply_filters( 'book-database/unique-slug', $new_slug, $slug, $taxonomy );

}