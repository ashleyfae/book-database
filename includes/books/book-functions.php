<?php
/**
 * Book Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Database\Books\BooksQuery;
use Book_Database\Exceptions\Exception;
use Book_Database\Models\Book;

/**
 * Get a single book by its ID
 *
 * @param int $book_id
 *
 * @return Book|false
 */
function get_book( $book_id ) {

	$query = new BooksQuery();

	return $query->get_item( $book_id );

}

/**
 * Get a single book by a column name/value combo
 *
 * @param string $column_name
 * @param mixed  $column_value
 *
 * @return Book|false
 */
function get_book_by( $column_name, $column_value ) {

	$query = new BooksQuery();

	return $query->get_item_by( $column_name, $column_value );

}

/**
 * Query for books
 *
 * Note: An alternative to this function is Books_Query::get_books().
 *
 * @see BooksQuery::get_books()
 *
 * Here's how they differ:
 *
 * ==> get_books()
 *      - Always returns `Book` objects.
 *      - Will join on the other tables if queries are passed in, but columns from those tables are
 *        never actually returned. So you can filter, but not select/orderby.
 *      - Once you have a `Book` object you will have to do additional queries to get author/series
 *        information becuase it's stored in a different table.
 *      - Integrates with WP_Object_Cache.
 *
 * ==> Books_Query::get_books()
 *      - Returns a generic object.
 *      - Always joins on the author and series table so that information is retrieved at the same time.
 *      - By default also joins on the reading log table and returns the average rating for the book.
 *      - No WP_Object_Cache support.
 *
 * As an example, I prefer to use Books_Query::get_books() on the admin list table because I want to
 * have 20 results per page and each page ALWAYS includes author, series, and rating details. Using
 * Books_Query::get_books() is therefore faster because you only need one query.
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type int          $cover_id            Filter by cover ID. Default empty.
 * @type string       $title               Filter by title. Default empty.
 * @type string       $index_title         Filter by index title. Default empty.
 * @type int          $series_id           Filter by series ID. Default empty.
 * @type array        $series_id__in       An array of series IDs to include. Default empty.
 * @type array        $series_id__not_in   An array of series IDs to exclude. Default empty.
 * @type int|float    $series_position     Filter by position in the series. Default empty.
 * @type int          $pages               Filter by number of pages.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type array        $author_query        Query for authors. See WP_Tax_Query.
 * @type array        $series_query        Query for series. See \Book_Database\BerlinDB\Database\Queries\Series.
 * @type array        $tax_query           Query for taxonomy terms. See WP_Tax_Query.
 * @type array        $edition_query       Query for editions. See \Book_Database\BerlinDB\Database\Queries\Edition.
 * @type array        $reading_log_query   Query for reading logs. See
 *       \Book_Database\BerlinDB\Database\Queries\Reading_Log.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'cover_id', 'title', 'index_title', 'series_id',
 *                                         'series_position', 'pub_date', 'pages', 'date_created', and
 *                                         'date_modified'. Also accepts false, an empty array, or 'none'
 *                                          to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Book[] Array of Book objects.
 */
function get_books( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'number' => 20
	) );

	$query = new BooksQuery();

	return $query->query( $args );

}

/**
 * Count the books
 *
 * @param array $args
 *
 * @see get_books() for accepted arguments.
 *
 * @return int
 */
function count_books( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'count' => true
	) );

	$query = new BooksQuery( $args );

	return absint( $query->found_items );

}

/**
 * Add a new book
 *
 * @param array           $args            {
 *
 * @type int              $cover_id        Optional. Attachment ID of the book cover.
 * @type string           $title           Required. Title of the book.
 * @type string           $index_title     Title used in archives.
 * @type array|int|string $authors         Single author name/ID or array of author names/IDs.
 * @type int              $series_id       ID of the series this book is part of.
 * @type int|float|null   $series_position This book's position in the series.
 * @type string           $pub_date        The book's publication date, in MySQL / UTC format.
 * @type int              $pages           Number of pages in the book.
 * @type string           $synopsis        Synopsis.
 * @type string           $goodreads_url   Goodreads URL.
 * @type array            $terms           Multi-dimensional array of terms to apply. Key is the taxonomy
 *                                         and value is a single term name/ID or array of term names/IDs.
 * }
 *
 * @return int ID of the newly created book.
 * @throws Exception
 */
function add_book( $args = array() ) {

	$args = wp_parse_args( $args, array(
		'cover_id'        => 0,
		'title'           => '',
		'index_title'     => '',
		'authors'         => '',
		'series_id'       => null,
		'series_position' => null,
		'pub_date'        => '',
		'pages'           => null,
		'synopsis'        => '',
		'goodreads_url'   => '',
		'terms'           => array()
	) );

	if ( empty( $args['title'] ) ) {
		throw new Exception( 'missing_parameter', __( 'Book title is required.', 'book-database' ), 400 );
	}

	$query   = new BooksQuery();
	$book_id = $query->add_item( $args );

	if ( empty( $book_id ) ) {
		throw new Exception( 'database_error', __( 'Failed to insert new book into the database.', 'book-database' ), 500 );
	}

	if ( ! empty( $args['authors'] ) ) {
		set_book_authors( $book_id, $args['authors'] );
	}

	if ( ! empty( $args['terms'] ) ) {
		foreach ( $args['terms'] as $taxonomy => $terms ) {
			set_book_terms( $book_id, $terms, $taxonomy );
		}
	}

	return absint( $book_id );

}

/**
 * Update an existing book
 *
 * @param int   $book_id ID of the book to update.
 * @param array $args    Arguments to update.
 *
 * @return bool
 * @throws Exception
 */
function update_book( $book_id, $args = array() ) {

	$query   = new BooksQuery();
	$updated = $query->update_item( $book_id, $args );

	if ( ! $updated ) {
		throw new Exception( 'database_error', __( 'Failed to update the book.', 'book-database' ), 500 );
	}

	return true;

}

/**
 * Delete a book
 *
 * This also deletes any data linking to this book, such as book-term relationships,
 * owned editions, reading logs, and reviews.
 *
 * @param int $book_id ID of the book to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book( $book_id ) {

	global $wpdb;

	$query   = new BooksQuery();
	$deleted = $query->delete_item( $book_id );

	if ( ! $deleted ) {
		throw new Exception( 'database_error', __( 'Failed to delete the book.', 'book-database' ), 500 );
	}

	$ar_table       = book_database()->get_table( 'book_author_relationships' )->get_table_name();
	$bt_table       = book_database()->get_table( 'book_term_relationships' )->get_table_name();
	$ed_table       = book_database()->get_table( 'editions' )->get_table_name();
	$link_table     = book_database()->get_table( 'book_links' )->get_table_name();
	$log_table      = book_database()->get_table( 'reading_log' )->get_table_name();
	$rev_table      = book_database()->get_table( 'reviews' )->get_table_name();
	$rev_meta_table = book_database()->get_table( 'review_meta' )->get_table_name();
	$bmeta_table    = book_database()->get_table( 'book_meta' )->get_table_name();

	// Delete all book-author relationships for this book.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$ar_table} WHERE book_id = %d", $book_id ) );

	// Delete all book-term relationships for this book.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$bt_table} WHERE book_id = %d", $book_id ) );

	// Delete all editions of this book.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$ed_table} WHERE book_id = %d", $book_id ) );

	// Delete all reading logs of this book.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$log_table} WHERE book_id = %d", $book_id ) );

	// Delete all reviews & review meta of this book.
	$wpdb->query( $wpdb->prepare( "DELETE review, meta FROM {$rev_table} AS review LEFT JOIN {$rev_meta_table} AS meta ON( review.id = meta.bdb_review_id ) WHERE book_id = %d", $book_id ) );

	// Delete all book meta.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$bmeta_table} WHERE bdb_book_id = %d", $book_id ) );

	// Delete all links to this book.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$link_table} WHERE book_id = %d", $book_id ) );

	return true;

}

/**
 * Get the Book Library admin page URL.
 *
 * @param array $args Query args to append to the URL.
 *
 * @return string
 */
function get_books_admin_page_url( $args = array() ) {

	$sanitized_args = array();

	foreach ( $args as $key => $value ) {
		$sanitized_args[ sanitize_key( $key ) ] = urlencode( $value );
	}

	return add_query_arg( $sanitized_args, admin_url( 'admin.php?page=bdb-books' ) );

}

/**
 * Generate an "index title". This moves "The", "A", and "An" to the end of the title.
 *
 * Before: A History of Hobbits
 * After: History of Hobbits, A
 *
 * @param string $original_title Title to convert.
 *
 * @return string
 */
function generate_book_index_title( $original_title ) {

	$index_title = '';

	if ( 'The ' === substr( $original_title, 0, 4 ) ) {
		$index_title = substr( $original_title, 4 ) . ', ' . __( 'The', 'book-database' );
	} elseif ( 'A ' === substr( $original_title, 0, 2 ) ) {
		$index_title = substr( $original_title, 2 ) . ', ' . __( 'A', 'book-database' );
	} elseif ( 'An ' === substr( $original_title, 0, 3 ) ) {
		$index_title = substr( $original_title, 3 ) . ', ' . __( 'An', 'book-database' );
	}

	/**
	 * Filters the generated index title.
	 *
	 * @param string $index_title    Newly created index title.
	 * @param string $original_title Original book title.
	 */
	return apply_filters( 'book-database/book/generate-index-title', $index_title, $original_title );

}
