<?php
/**
 * Book Layout Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get an array of all the available book information fields, their
 * placeholder values, and their default labels.
 *
 * @return array
 */
function get_book_fields() {

	$fields = array(
		'cover'         => array(
			'name'        => __( 'Cover Image', 'book-database' ),
			'placeholder' => '[cover]',
			'label'       => '[cover]',
			'alignment'   => 'left', // left, center, right
			'size'        => 'full' // thumbnail, medium, large, full
		),
		'title'         => array(
			'name'        => __( 'Book Title', 'book-database' ),
			'placeholder' => '[title]',
			'label'       => '<strong>[title]</strong>',
		),
		'author'        => array(
			'name'        => __( 'Author', 'book-database' ),
			'placeholder' => '[author]',
			'label'       => sprintf( __( ' by %s', 'book-database' ), '[author]' ),
			'linebreak'   => 'on'
		),
		'series'        => array(
			'name'        => __( 'Series Name', 'book-database' ),
			'placeholder' => '[series]',
			'label'       => sprintf( __( '<strong>Series:</strong> %s', 'book-database' ), '[series]' ),
			'linebreak'   => 'on'
		),
		'pub_date'      => array(
			'name'        => __( 'Pub Date', 'book-database' ),
			'placeholder' => '[pub_date]',
			'label'       => sprintf( __( '<strong>Publication Date:</strong> %s', 'book-database' ), '[pub_date]' ),
			'linebreak'   => 'on'
		),
		'pages'         => array(
			'name'        => __( 'Pages', 'book-database' ),
			'placeholder' => '[pages]',
			'label'       => sprintf( __( '<strong>Pages:</strong> %s', 'book-database' ), '[pages]' ),
			'linebreak'   => 'on'
		),
		'goodreads_url' => array(
			'name'        => __( 'Goodreads', 'book-database' ),
			'placeholder' => '[goodreads]',
			'label'       => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', '[goodreads]', __( 'Goodreads', 'book-database' ) ),
			'linebreak'   => 'on'
		),
		'buy_link'      => array(
			'name'        => __( 'Purchase Links', 'book-database' ),
			'placeholder' => '[buy]',
			'label'       => sprintf( __( '<strong>Buy the Book:</strong> %s', 'book-database' ), '[buy]' ),
			'linebreak'   => 'on'
		),
		'rating'        => array(
			'name'        => __( 'Rating', 'book-database' ),
			'placeholder' => '[rating]',
			'label'       => sprintf( __( '<strong>Rating:</strong> %s', 'book-database' ), '[rating]' ),
			'linebreak'   => 'on'
		),
		'synopsis'      => array(
			'name'        => __( 'Synopsis', 'book-database' ),
			'placeholder' => '[synopsis]',
			'label'       => '<blockquote>[synopsis]</blockquote>',
		),
	);

	/**
	 * Filters the available fields.
	 *
	 * @param array $fields
	 */
	return apply_filters( 'book-database/book/available-fields', $fields );

}

/**
 * Add taxonomies to book layout fields
 *
 * @param array $fields
 *
 * @return array
 */
function book_layout_taxonomy_fields( $fields ) {

	$taxonomies = get_book_taxonomies( array(
		'number' => 9999
	) );

	foreach ( $taxonomies as $taxonomy ) {
		if ( isset( $fields[ $taxonomy->get_slug() ] ) ) {
			continue;
		}

		$fields[ $taxonomy->get_slug() ] = array(
			'name'        => $taxonomy->get_name(),
			'placeholder' => '[' . sanitize_key( $taxonomy->get_slug() ) . ']',
			'label'       => sprintf( '<strong>%s:</strong> [%s]', esc_html( $taxonomy->get_name() ), esc_html( sanitize_key( $taxonomy->get_slug() ) ) ),
			'linebreak'   => 'on'
		);
	}

	return $fields;

}

add_filter( 'book-database/book/available-fields', __NAMESPACE__ . '\book_layout_taxonomy_fields' );

/**
 * Get the enabled book fields
 *
 * @return array
 */
function get_enabled_book_fields() {
	return bdb_get_option( 'book_layout', get_book_fields() );
}

/**
 * Get the book cover alignment options
 *
 * @return array
 */
function get_book_cover_alignment_options() {
	return array(
		'left'   => __( 'Left', 'book-database' ),
		'center' => __( 'Centered', 'book-database' ),
		'right'  => __( 'Right', 'book-database' )
	);
}

/**
 * Get an array of available image sizes
 *
 * @return array
 */
function get_book_cover_image_sizes() {

	$sizes       = get_intermediate_image_sizes();
	$final_sizes = array( 'full' => esc_html__( 'full', 'book-database' ) );
	if ( is_array( $sizes ) ) {
		foreach ( $sizes as $size ) {
			$final_sizes[ $size ] = $size;
		}
	}

	return $final_sizes;

}