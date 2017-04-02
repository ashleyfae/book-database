<?php
/**
 * Book Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Get Books
 *
 * @param array $args Query arguments to override the defaults.
 *
 * @since 1.0.0
 * @return array Array of book objects.
 */
function bdb_get_books( $args = array() ) {
	$books = book_database()->books->get_books( $args );

	return apply_filters( 'book-database/get-books', $books, $args );
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
 * Get Book Author Name(s)
 *
 * @uses  bdb_get_book_author()
 *
 * @param int   $book_id ID of the book to get the author for.
 * @param array $args    Query arguments to override the defaults.
 *
 * @since 1.0.0
 * @return string|false Comma-separated list of author names or false on failure.
 */
function bdb_get_book_author_name( $book_id, $args = array() ) {
	$terms = bdb_get_book_author( $book_id, $args );
	$names = false;

	if ( is_array( $terms ) ) {
		$names_temp = array();

		foreach ( $terms as $term ) {
			$names_temp[] = $term->name;
		}

		$names = implode( ', ', $names_temp );
	}

	return apply_filters( 'book-database/get-book-author-name', $names, $terms, $book_id, $args );
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
 * @param int          $book_id     ID of the book to get the series for.
 * @param string|false $series_name Optionally pass the series name if you already know it.
 *
 * @since 1.0.0
 * @return string|false Name of the series with the position appended, or false on failure.
 */
function bdb_get_formatted_series_name( $book_id, $series_name = false ) {
	$series_name    = $series_name ? $series_name : bdb_get_book_series_name( $book_id, true );
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
 * Insert New Book
 *
 * If the `ID` key is passed into the `$data` array then an existing book
 * is updated instead.
 *
 * @param array $data   Book data. Arguments include:
 *                      `ID` - To update an existing book.
 *                      `cover` - Book cover attachment ID.
 *                      `title` - Title of the book.
 *                      `index_title` - Title used in indexing.
 *                      `series_id` - ID of the series.
 *                      `series_position` - Position in the series.
 *                      `series_name` - Use instead of `series_id` to create a new series.
 *                      `pub_date` - Publication date.
 *                      `pages` - Number of pages.
 *                      `synopsis` - Book synopsis.
 *                      `goodreads_url` - Link to Goodreads page.
 *                      `buy_link` - Link to purchase page.
 *                      `terms` - Array of associated terms.
 *                      |----> `term_type` - Array of terms names of this type.
 *
 * @since 1.0.0
 * @return int|WP_Error ID of the book inserted or updated, or WP_Error on failure.
 */
function bdb_insert_book( $data = array() ) {

	$book_db_data = array();

	/* Series Table */

	// If series name is given, let's add a new series.
	if ( array_key_exists( 'series_name', $data ) && $data['series_name'] ) {
		$series = book_database()->series->get_series_by( 'name', wp_unslash( $data['series_name'] ) );

		if ( ! empty( $series ) ) {
			$series_id = absint( $series->ID );
		} else {
			$series_id = bdb_insert_series( wp_unslash( $data['series_name'] ) );
		}

		if ( $series_id ) {
			$data['series_id'] = absint( $series_id );
		}
	}

	/* Book Table */

	$pub_date = null;

	if ( array_key_exists( 'pub_date', $data ) && $data['pub_date'] ) {
		$pub_date = date( 'Y-m-d H:i:s', strtotime( $data['pub_date'] ) );
	}

	$book_db_data['cover']           = ( array_key_exists( 'cover', $data ) && is_numeric( $data['cover'] ) ) ? absint( $data['cover'] ) : 0;
	$book_db_data['title']           = array_key_exists( 'title', $data ) ? sanitize_text_field( wp_strip_all_tags( $data['title'] ) ) : '';
	$book_db_data['index_title']     = array_key_exists( 'index_title', $data ) ? sanitize_text_field( wp_strip_all_tags( $data['index_title'] ) ) : $data['title'];
	$book_db_data['series_id']       = ( array_key_exists( 'series_id', $data ) && $data['series_id'] ) ? absint( $data['series_id'] ) : null;
	$book_db_data['series_position'] = ( array_key_exists( 'series_position', $data ) && $data['series_position'] != '' ) ? sanitize_text_field( wp_strip_all_tags( $data['series_position'] ) ) : null;
	$book_db_data['pub_date']        = $pub_date;
	$book_db_data['pages']           = ( array_key_exists( 'pages', $data ) && $data['pages'] ) ? absint( $data['pages'] ) : null;
	$book_db_data['synopsis']        = array_key_exists( 'synopsis', $data ) ? wp_kses_post( $data['synopsis'] ) : '';
	$book_db_data['goodreads_url']   = array_key_exists( 'goodreads_url', $data ) ? esc_url_raw( $data['goodreads_url'] ) : '';
	$book_db_data['buy_link']        = array_key_exists( 'buy_link', $data ) ? esc_url_raw( $data['buy_link'] ) : '';
	$book_db_data['terms']           = ( array_key_exists( 'terms', $data ) && is_array( $data['terms'] ) ) ? $data['terms'] : array();

	if ( array_key_exists( 'ID', $data ) && $data['ID'] > 0 ) {
		$book_db_data['ID'] = absint( $data['ID'] );
	}

	$book_db_data = wp_unslash( $book_db_data );

	$book_id = book_database()->books->add( $book_db_data );

	if ( ! $book_id ) {
		return new WP_Error( 'error-inserting-book', __( 'Error inserting book information into database.', 'book-database' ) );
	}

	/* Set Terms */

	$terms      = array();
	$taxonomies = bdb_get_taxonomies( true );
	foreach ( $taxonomies as $id => $options ) {
		$terms[ $id ] = array();
	}

	$final_terms = array_merge( $terms, $book_db_data['terms'] );

	foreach ( $final_terms as $type => $terms ) {
		bdb_set_book_terms( $book_id, $terms, $type, false );
	}

	return $book_id;

}

/**
 * Insert Series
 *
 * @param string $series_name Name of the series.
 * @param string $description Series description.
 *
 * @since 1.0.0
 * @return int ID of the series.
 */
function bdb_insert_series( $series_name, $description = '' ) {
	$series_exists = book_database()->series->get_series_by( 'name', $series_name );

	if ( $series_exists && is_object( $series_exists ) ) {
		return $series_exists->ID;
	}

	$new_series_id = book_database()->series->add( array(
		'name'        => sanitize_text_field( wp_strip_all_tags( $series_name ) ),
		'description' => wp_kses_post( $description )
	) );

	return $new_series_id;
}

/**
 * Get Series
 *
 * Returns a single series.
 *
 * @param array $args Query arguments to override the defaults.
 *
 * @since 1.0.0
 * @return object|false Database object or false.
 */
function bdb_get_series( $args = array() ) {
	$defaults   = array(
		'number' => 1
	);
	$args       = wp_parse_args( $args, $defaults );
	$all_series = book_database()->series->get_series( $args );

	if ( ! is_array( $all_series ) ) {
		return false;
	}

	return $all_series[0];
}

/**
 * Generate Alternative Book Title
 *
 * For example:
 * The Winter King => Winter King, The
 *
 * @param string $title
 *
 * @since 1.0.0
 * @return string|false
 */
function bdb_generate_alternative_book_title( $title ) {

	$alternate_title = false;

	if ( 'The ' == substr( $title, 0, 4 ) ) {
		$alternate_title = substr( $title, 4 ) . ', ' . esc_html__( 'The', 'book-database' );
	} elseif ( 'A ' == substr( $title, 0, 2 ) ) {
		$alternate_title = substr( $title, 2 ) . ', ' . esc_html__( 'A', 'book-database' );
	} elseif ( 'An ' == substr( $title, 0, 3 ) ) {
		$alternate_title = substr( $title, 3 ) . ', ' . esc_html__( 'Am', 'book-database' );
	}

	return apply_filters( 'book-database/book/generate-alternative-title', $alternate_title, $title );

}