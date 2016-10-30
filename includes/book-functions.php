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
 * @param int              $book_id ID of the book to relate terms to.
 * @param array|int|string $terms   Single term ID or array of IDs.
 * @param string           $type    Term type (`author`, `publisher`, etc.).
 * @param bool             $append  If false, will delete the difference of terms.
 *
 * @since 1.0.0
 * @return array|WP_Error Term IDs of the affected terms.
 */
function bdb_set_book_terms( $book_id, $terms, $type, $append = false ) {

	if ( ! is_numeric( $book_id ) || ! $book_id > 0 ) {
		return new WP_Error( 'invalid_book_id', __( 'Invalid book ID.', 'book-database' ) );
	}

	if ( ! is_array( $terms ) ) {
		$terms = array( $terms );
	}

	// Get existing terms.
	if ( ! $append ) {
		$old_term_ids = bdb_get_book_terms( $book_id, $type, array( 'fields' => 'ids' ) );
	} else {
		$old_term_ids = array();
	}

	$all_term_ids = array();

	foreach ( (array) $terms as $term ) {

		// $term is either a term name or ID.

		if ( ! strlen( trim( $term ) ) ) {
			continue;
		}

		if ( is_numeric( $term ) ) {

			// We have a term ID.

			$term_id = absint( $term );

			// If this term ID doesn't exist - skip it.
			if ( ! book_database()->book_terms->exists( $term_id ) ) {
				continue;
			}

			// Update the count.
			bdb_update_term_count( $term_id );

			$all_term_ids[] = $term_id;

		} else {

			// We have a term name.

			// Check to see if it already exists.
			$existing_term = book_database()->book_terms->get_term_by( 'name', $term );

			if ( $existing_term ) {
				$term_id = $existing_term->term_id;
			} else {
				// Create new term.
				$term_id = book_database()->book_terms->add( array(
					'name' => sanitize_text_field( $term ),
					'type' => sanitize_text_field( $type )
				) );
			}

			// Error adding it.
			if ( ! $term_id ) {
				continue;
			}

			$all_term_ids[] = $term_id;

		}

		// If the term relationship already exists - let's move on.
		if ( bdb_relationship_exists( $book_id, $term_id ) ) {
			continue;
		}

		// Otherwise, create the relationship.
		book_database()->book_term_relationships->add( array(
			'term_id' => absint( $term_id ),
			'book_id' => absint( $book_id ),
			'count'   => 1
		) );

	}

	if ( ! $append ) {

		// Delete existing term relationships.
		$delete_term_relationships = array_diff( $old_term_ids, $all_term_ids );

		if ( $delete_term_relationships ) {
			foreach ( $delete_term_relationships as $term_id ) {
				// Delete the relationship.
				book_database()->book_term_relationships->delete( absint( $term_id ) ); // @todo do this without a foreach

				// Reduce the count.
				bdb_update_term_count( $term_id );
			}
		}

	}

	return $all_term_ids;

}

/**
 * Relationship Exists
 *
 * Checks whether a relationship exists between a book ID and a term ID.
 *
 * @param int $book_id
 * @param int $term_id
 *
 * @since 1.0.0
 * @return bool
 */
function bdb_relationship_exists( $book_id, $term_id ) {
	if ( ! is_numeric( $book_id ) || ! is_numeric( $term_id ) ) {
		return false;
	}

	$args   = array(
		'term_id' => absint( $term_id ),
		'book_id' => absint( $book_id )
	);
	$result = book_database()->book_term_relationships->get_relationships( apply_filters( 'book-database/relationship-exists-args', $args, $book_id, $term_id ) );

	return ( is_array( $result ) && count( $result ) ) ? true : false;
}

/**
 * Update Term Count
 *
 * @param int $term_id ID of the term.
 *
 * @since 1.0.0
 * @return int|bool Updated term ID on success, or false on failure.
 */
function bdb_update_term_count( $term_id ) {
	$new_count = book_database()->book_term_relationships->count( array(
		'term_id' => $term_id
	) );

	if ( false === $new_count ) {
		return false;
	}

	$args = array(
		'term_id' => absint( $term_id ),
		'count'   => absint( $new_count )
	);

	return book_database()->book_terms->add( $args );
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
 *                      `series_id` - ID of the series.
 *                      `series_position` - Position in the series.
 *                      `series_name` - Use instead of `series_id` to create a new series.
 *                      `pub_date` - Publication date.
 *                      `synopsis` - Book synopsis.
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
	if ( array_key_exists( 'series_name', $data ) && ! array_key_exists( 'series_id', $data ) ) {
		$series_id = bdb_insert_series( $data['series_name'] );

		if ( $series_id ) {
			$data['series_id'] = absint( $series_id );
		}
	}

	/* Book Table */

	$pub_date = null;

	if ( array_key_exists( 'pub_date', $data ) ) {
		$pub_date = date( 'Y-m-d H:i:s', strtotime( $data['pub_date'] ) );
	}

	$book_db_data['cover']           = ( array_key_exists( 'cover', $data ) && is_numeric( $data['cover'] ) ) ? absint( $data['cover'] ) : 0;
	$book_db_data['title']           = array_key_exists( 'title', $data ) ? sanitize_text_field( wp_strip_all_tags( $data['title'] ) ) : '';
	$book_db_data['series_id']       = array_key_exists( 'series_id', $data ) ? absint( $data['series_id'] ) : null;
	$book_db_data['series_position'] = array_key_exists( 'series_position', $data ) ? sanitize_text_field( wp_strip_all_tags( $data['series_position'] ) ) : null;
	$book_db_data['pub_date']        = $pub_date;
	$book_db_data['synopsis']        = array_key_exists( 'synopsis', $data ) ? wp_kses_post( $data['synopsis'] ) : '';

	if ( array_key_exists( 'ID', $data ) && $data['ID'] > 0 ) {
		$book_db_data['ID'] = absint( $data['ID'] );
	}

	$book_id = book_database()->books->add( $book_db_data );

	if ( ! $book_id ) {
		return new WP_Error( 'error-inserting-book', __( 'Error inserting book information into database.', 'book-database' ) );
	}

	// @todo Insert terms.

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