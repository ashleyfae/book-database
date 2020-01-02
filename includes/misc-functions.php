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
	$local_date = get_date_from_gmt( $date, 'Y-m-d H:i:s' );

	return date( $format, strtotime( $local_date ) );

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
 * @param string $taxonomy Accepts any taxonomy slug, `author`, `series`, or `book_taxonomy`.
 *
 * @return string A unique slug.
 */
function unique_book_slug( $slug, $taxonomy = 'author' ) {

	// Check if this slug already exists.
	if ( 'series' === $taxonomy ) {
		$terms = get_book_series_by( 'slug', $slug );
	} elseif ( 'book_taxonomy' === $taxonomy ) {
		$terms = get_book_taxonomy_by( 'slug', $slug );
	} elseif ( 'author' == $taxonomy ) {
		$terms = get_book_author_by( 'slug', $slug );
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
				$terms = get_book_series_by( 'slug', $alt_slug );
			} elseif ( 'book_taxonomy' === $taxonomy ) {
				$terms = get_book_taxonomy_by( 'slug', $alt_slug );
			} elseif ( 'author' === $taxonomy ) {
				$terms = get_book_author_by( 'slug', $alt_slug );
			} else {
				$terms = count_book_terms( array(
					'taxonomy' => $taxonomy,
					'slug'     => $alt_slug
				) );
			}

			$suffix ++;

		} while ( $terms );

		$new_slug = $alt_slug;
	}

	return apply_filters( 'book-database/unique-slug', $new_slug, $slug, $taxonomy );

}

/**
 * Whether or not terms should link to the archive
 *
 * Disable with this:
 *      `add_filter( 'book-database/link-terms', '__return_false' );`
 *
 * @return bool
 */
function link_book_terms() {
	return apply_filters( 'book-database/link-terms', true );
}

/**
 * Get the term archive link
 *
 * @param Author|Book_Term|Series|Rating $term
 *
 * @return bool
 */
function get_book_term_link( $term ) {

	if ( ! is_object( $term ) ) {
		return false;
	}

	$slug     = method_exists( $term, 'get_slug' ) ? $term->get_slug() : '';
	$taxonomy = '';

	if ( $term instanceof Author ) {
		$taxonomy = 'author';
	} elseif ( $term instanceof Book_Term ) {
		$taxonomy = $term->get_taxonomy();
	} elseif ( $term instanceof Series ) {
		$taxonomy = 'series';
	} elseif ( $term instanceof Rating ) {
		$taxonomy = 'rating';
		$slug     = $term->get_rating();
	}

	if ( empty( $taxonomy ) || empty( $slug ) ) {
		return false;
	}

	$base_url  = untrailingslashit( get_reviews_page_url() );
	$final_url = sprintf( '%1$s/%2$s/%3$s/', $base_url, urlencode( $taxonomy ), urlencode( $slug ) );

	/**
	 * Filters the term archive URL.
	 *
	 * @param string                         $final_url Final archive URL.
	 * @param string                         $slug      Term slug.
	 * @param string                         $taxonomy  Term taxonomy / type.
	 * @param Author|Book_Term|Series|Rating $term      Term object.
	 */
	return apply_filters( 'book-database/term-archive-link', $final_url, $slug, $taxonomy, $term );

}

/**
 * Get the site's timezone
 *
 * @return \DateTimeZone
 */
function get_site_timezone() {
	if ( function_exists( 'wp_timezone' ) ) {
		return wp_timezone();
	} else {
		return new \DateTimeZone( get_option( 'timezone_string' ) );
	}
}