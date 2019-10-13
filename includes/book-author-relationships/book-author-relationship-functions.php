<?php
/**
 * Book Author Relationship Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get a single book-author-relationship by its ID
 *
 * @param int $relationship_id
 *
 * @return Book_Author_Relationship|false
 */
function get_book_author_relationship( $relationship_id ) {

	$query = new Book_Author_Relationships_Query();

	return $query->get_item( $relationship_id );

}

/**
 * Get a book-author-relationship by book ID and author ID.
 *
 * @param int   $book_id   ID of the book.
 * @param int   $author_id ID of the author.
 * @param array $args      Query arguments to override the defaults.
 *
 * @return Book_Author_Relationship|false|mixed
 */
function get_book_author_relationship_by_book_and_author( $book_id, $author_id, $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number'    => 1,
		'author_id' => $author_id,
		'book_id'   => $book_id
	) );

	$relationships = get_book_author_relationships( $args );

	if ( empty( $relationships ) ) {
		return false;
	}

	return reset( $relationships );

}

/**
 * Query for book-author-relationships
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type int          $author_id           Filter by author ID. Default empty.
 * @type int          $book_id             Filter by book ID. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'author_id', 'book_id', 'date_created', and
 *                                         'date_modified'. Also accepts false, an empty array, or 'none'
 *                                          to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Book_Author_Relationship[] Array of Book_Author_Relationship objects.
 */
function get_book_author_relationships( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new Book_Author_Relationships_Query();

	return $query->query( $args );

}

/**
 * Count the book-author-relationships
 *
 * @param array $args
 *
 * @see get_book_author_relationships() for accepted arguments.
 *
 * @return int
 */
function count_book_author_relationships( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new Book_Author_Relationships_Query( $args );

	return absint( $query->found_items );

}

/**
 * Add a new book-author-relationship
 *
 * @param array $args      {
 *
 * @type int    $author_id ID of the author.
 * @type int    $book_id   ID of the book.
 * }
 *
 * @return int ID of the newly created relationship.
 * @throws Exception
 */
function add_book_author_relationship( $args ) {

	$args = wp_parse_args( $args, array(
		'author_id' => 0,
		'book_id'   => 0
	) );

	if ( empty( $args['author_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'An author ID is required.', 'book-database' ), 400 );
	}

	if ( empty( $args['book_id'] ) ) {
		throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
	}

	$query           = new Book_Author_Relationships_Query();
	$relationship_id = $query->add_item( $args );

	if ( empty( $relationship_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new relationship into the database.', 'book-database' ), 500 );
	}

	$relationship = get_book_author_relationship( $relationship_id );

	/**
	 * Triggers when a relationship is successfully added.
	 *
	 * @param int $relationship_id ID of the relationship.
	 * @param int $author_id       ID of the author.
	 * @param int $book_id         ID of the book.
	 */
	do_action( 'book-database/book-author-relationship/added', $relationship_id, $relationship->get_author_id(), $relationship->get_book_id() );

	return absint( $relationship_id );

}

/**
 * Delete a book-author-relationship
 *
 * @param int $relationship_id ID of the relationship to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_author_relationship( $relationship_id ) {

	$relationship = get_book_author_relationship( $relationship_id );

	if ( ! $relationship instanceof Book_Author_Relationship ) {
		return true;
	}

	$query   = new Book_Author_Relationships_Query();
	$deleted = $query->delete_item( $relationship_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the relationship.', 'book-database' ), 500 );
	}

	/**
	 * Triggers when a relationship is successfully deleted.
	 *
	 * @param int $relationship_id ID of the relationship.
	 * @param int $author_id       ID of the author.
	 * @param int $book_id         ID of the book.
	 */
	do_action( 'book-database/book-author-relationship/deleted', $relationship_id, $relationship->get_author_id(), $relationship->get_book_id() );

	return true;

}

/**
 * Get the authors associated with a book
 *
 * @param int   $book_id   ID of the book to get the terms of.
 * @param array $args      {
 *                         Query arguments to override the defaults.
 *
 * @type string $orderby   Column to order by.
 * @type string $order     Order.
 * @type string $fields    Column names to return.
 * }
 *
 * @return Author[]|array
 */
function get_attached_book_authors( $book_id, $args = array() ) {

	global $wpdb;

	$args = wp_parse_args( $args, array(
		'orderby' => 'name',
		'order'   => 'ASC',
		'fields'  => ''
	) );

	$relationships_table = book_database()->get_table( 'book_author_relationships' )->get_table_name();
	$authors_table       = book_database()->get_table( 'authors' )->get_table_name();

	// Select this.
	$select_this = 'a.*';
	if ( in_array( $args['fields'], array( 'id', 'ids' ) ) ) {
		$select_this = 'a.id';
	} elseif ( in_array( $args['fields'], array( 'name', 'names' ) ) ) {
		$select_this = 'a.name';
	}

	// Orderby
	$orderby = $args['orderby'];
	$order   = $args['order'];

	if ( in_array( $orderby, array(
		'id',
		'name',
		'slug',
		'image_id',
		'book_count',
		'date_created',
		'date_modified'
	) ) ) {
		$orderby = "a.$orderby";
	} elseif ( 'none' === $orderby ) {
		$orderby = '';
		$order   = '';
	} else {
		$orderby = 'a.id';
	}

	if ( ! empty( $orderby ) ) {
		$orderby = "ORDER BY $orderby";
	}

	$order = strtoupper( $order );
	if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
		$order = 'ASC';
	}

	$query   = $wpdb->prepare( "SELECT {$select_this} FROM {$authors_table} AS a INNER JOIN {$relationships_table} AS ar on a.id = ar.author_id WHERE ar.book_id = %d $orderby $order", absint( $book_id ) );
	$authors = array();

	if ( empty( $args['fields'] ) ) {
		$objects = $wpdb->get_results( $query );

		if ( is_array( $objects ) ) {
			foreach ( $objects as $object ) {
				$authors[] = new Author( $object );
			}
		}
	} elseif ( in_array( $args['fields'], array( 'id', 'ids', 'name', 'names' ) ) ) {
		$authors = $wpdb->get_col( $query );
	}

	return $authors;

}

/**
 * Create book and author relationships
 *
 * @param int              $book_id ID of the book to add terms to.
 * @param array|int|string $authors Single author name/ID, or array of author names/IDs.
 * @param bool             $append  False to delete the difference of authors, true to append to the existing.
 *
 * @throws Exception
 */
function set_book_authors( $book_id, $authors, $append = false ) {

	global $wpdb;

	if ( ! is_array( $authors ) ) {
		$authors = array( $authors );
	}

	// Get existing authors.
	if ( ! $append ) {
		$old_author_ids = get_attached_book_authors( $book_id, array( 'fields' => 'id' ) );
	} else {
		$old_author_ids = array();
	}

	$all_author_ids = array();

	foreach ( $authors as $author ) {

		// $author is either an author name or ID

		if ( ! strlen( trim( $author ) ) ) {
			continue;
		}

		if ( is_int( $author ) ) {

			// We have an author ID.

			$author_id = absint( $author );

			// If this author doesn't exist - skip it.
			if ( ! get_book_author( $author_id ) ) {
				continue;
			}

			$all_author_ids[] = $author_id;

		} else {

			// We have an author name.

			// Check to see if it already exists.
			$author_obj = get_book_author_by( 'name', $author );

			if ( empty( $author_obj ) ) {
				// Create a new author.
				$author_id = add_book_author( array(
					'name' => $author
				) );
			} else {
				$author_id = $author_obj->get_id();
			}

			if ( empty( $author_id ) ) {
				continue;
			}

			$all_author_ids[] = $author_id;

		}

		// If the author relationship already exists, let's move on.
		if ( get_book_author_relationship_by_book_and_author( $book_id, $author_id, array( 'fields' => 'id' ) ) ) {
			continue;
		}

		// Otherwise, create the relationship.
		add_book_author_relationship( array(
			'author_id' => absint( $author_id ),
			'book_id'   => absint( $book_id )
		) );

	}

	if ( ! $append ) {

		// Delete the differing relationships.
		$delete_author_ids = array_diff( $old_author_ids, $all_author_ids );

		if ( $delete_author_ids ) {
			$relationship_table = book_database()->get_table( 'book_author_relationships' )->get_table_name();
			$author_id_string   = implode( ',', array_map( 'absint', $delete_author_ids ) );

			$query = $wpdb->prepare( "DELETE FROM {$relationship_table} WHERE book_id = %d AND author_id IN({$author_id_string})", $book_id );
			$wpdb->query( $query );
		}

	}

}