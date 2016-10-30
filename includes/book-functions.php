<?php
/**
 * Book Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

/**
 * Get Default Labels
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_default_labels() {
	$defaults = array(
		'singular' => __( 'Book', 'book-database' ),
		'plural'   => __( 'Books', 'book-database' )
	);

	return apply_filters( 'book-database/default-labels', $defaults );
}

/**
 * Get Singular Label
 *
 * @param bool $lowercase
 *
 * @uses  bdb_get_default_labels()
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_label_singular( $lowercase = false ) {
	$defaults = bdb_get_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
}

/**
 * Get Plural Label
 *
 * @param bool $lowercase
 *
 * @uses  bdb_get_default_labels()
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_label_plural( $lowercase = false ) {
	$defaults = bdb_get_default_labels();

	return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
}

/**
 * Get Book
 *
 * Returns a set up UBB book object or false on failure.
 *
 * @param int $book_id
 *
 * @since 1.0.0
 * @return BDB_Book|false
 */
function bdb_get_book( $book_id ) {
	$book = book_database()->books->get_book( $book_id );

	if ( $book ) {
		$final_book = new BDB_Book( $book->ID );
	} else {
		$final_book = false;
	}

	return apply_filters( 'book-database/get-book', $final_book, $book, $book_id );
}

/**
 * Get Book Author
 *
 * @param int   $book_id ID of the book to get the author for.
 * @param array $args    Query arguments to override the defaults.
 *
 * @uses  bdb_get_book_terms()
 *
 * @since 1.0.0
 * @return array|false Array of author objects or false on failure.
 */
function bdb_get_book_author( $book_id, $args = array() ) {
	return bdb_get_book_terms( $book_id, 'author', $args );
}

/**
 * Get Book Series Name
 *
 * Returns the name of the series.
 *
 * @param int  $book_id       ID of the book.
 * @param bool $with_position Whether or not to return the position.
 *
 * @since 1.0.0
 * @return string|array String of series name if `$with_position` is false. Otherwise array with the following keys:
 *                      `name` - Name of the series.
 *                      `series_position` - Position in the series.
 */
function bdb_get_book_series_name( $book_id, $with_position = false ) {
	global $wpdb;
	$book_table   = book_database()->books->table_name;
	$series_table = book_database()->series->table_name;

	$select_this = 'series.name';
	if ( $with_position ) {
		$select_this = 'series.name, book.series_position';
	}

	$query = $wpdb->prepare( "SELECT $select_this from $series_table as series INNER JOIN $book_table as book on series.ID = book.series_id WHERE book.ID = %d", absint( $book_id ) );

	$series_name = false;

	if ( $with_position ) {
		$series = $wpdb->get_results( $query );
	} else {
		$series = $wpdb->get_col( $query );
	}

	if ( is_array( $series ) && array_key_exists( 0, $series ) ) {
		$series_name = $series[0];
	}

	if ( is_object( $series_name ) ) {
		$series_name = (array) $series_name;
	}

	return $series_name;
}

/**
 * Get Formatted Series Name
 *
 * Returns the name of the series followed by # and the position. Example:
 * The Wrath & the Dawn #1
 *
 * @param int $book_id
 *
 * @since 1.0.0
 * @return string|false Name of the series with the position appended, or false on failure.
 */
function bdb_get_formatted_series_name( $book_id ) {
	$series_name    = bdb_get_book_series_name( $book_id, true );
	$formatted_name = false;

	if ( is_array( $series_name ) ) {
		if ( $series_name['series_position'] ) {
			$formatted_name = sprintf( '%s #%s', $series_name['name'], $series_name['series_position'] );
		} else {
			$formatted_name = $series_name['name'];
		}
	}

	return apply_filters( 'book-database/book/get-formatted-series-name', $formatted_name, $series_name, $book_id );
}

/**
 * Get Book Terms
 *
 * @see   wp_get_object_terms()
 *
 * @param int         $book_id ID of the book to get the terms for.
 * @param string|bool $type    Type of terms to retrieve, or false for all.
 * @param array       $args    Query arguments to override the defaults.
 *
 * @since 1.0.0
 * @return array|false Array of term objects or false on failure.
 */
function bdb_get_book_terms( $book_id, $type = false, $args = array() ) {
	global $wpdb;

	$default_args = array(
		'orderby' => 'name',
		'order'   => 'ASC',
		'fields'  => 'all'
	);

	$args = wp_parse_args( $args, $default_args );

	$relationship_table = $wpdb->prefix . 'bdb_book_term_relationships';
	$term_table         = $wpdb->prefix . 'bdb_book_terms';

	$where_type = $type ? $wpdb->prepare( " AND t.type = %s", sanitize_text_field( $type ) ) : '';

	// Select this.
	$select_this = '';
	if ( 'all' == $args['fields'] ) {
		$select_this = 't.*';
	} elseif ( 'ids' == $args['fields'] ) {
		$select_this = 't.term_id';
	} elseif ( 'names' == $args['fields'] ) {
		$select_this = 't.name';
	}

	// Orderby
	$orderby = $args['orderby'];
	$order   = $args['order'];

	if ( in_array( $orderby, array( 'term_id', 'type', 'name', 'count' ) ) ) {
		$orderby = "t.$orderby";
	} elseif ( 'none' === $orderby ) {
		$orderby = '';
		$order   = '';
	} else {
		$orderby = 't.term_id';
	}

	if ( ! empty( $orderby ) ) {
		$orderby = "ORDER BY $orderby";
	}

	$order = strtoupper( $order );
	if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
		$order = 'ASC';
	}

	$query = $wpdb->prepare( "SELECT $select_this FROM $term_table AS t INNER JOIN $relationship_table AS tr on t.term_id = tr.term_id WHERE tr.book_id = %d $where_type $orderby $order", absint( $book_id ) );
	$terms = array();

	if ( 'all' == $args['fields'] ) {
		$terms = $wpdb->get_results( $query );
	} elseif ( 'ids' == $args['fields'] || 'names' == $args['fields'] ) {
		$terms = $wpdb->get_col( $query );
	}

	return $terms;
}

/**
 * Create Book and Term Relationships
 *
 * Relates a book to a term and term type. Creates the
 * term if it doesn't already exist.
 *
 * @see   wp_set_object_terms()
 *
 * @param int       $book_id ID of the book to relate terms to.
 * @param array|int $terms   Single term ID or array of IDs.
 * @param string    $type    Term type (`author`, `publisher`, etc.).
 * @param bool      $append  If false, will delete the difference of terms.
 *
 * @since 1.0.0
 * @return array|WP_Error Term IDs of the affected terms.
 */
function bdb_set_book_terms( $book_id, $terms, $type, $append = false ) {

	global $wpdb;

	if ( ! is_numeric( $book_id ) || $book_id < 1 ) {
		return new WP_Error( 'invalid_book_id', __( 'Invalid book ID.', 'book-database' ) );
	}

	if ( ! is_array( $terms ) ) {
		$terms = array( $terms );
	}

	if ( ! $append ) {

	}

}