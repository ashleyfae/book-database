<?php

/**
 * Books DB Class
 *
 * This class is for interacting with the books database table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Books extends BDB_DB {

	/**
	 * BDB_DB_Books constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'bdb_books';
		$this->primary_key = 'ID';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'ID'              => '%d',
			'cover'           => '%d',
			'title'           => '%s',
			'index_title'     => '%s',
			'series_id'       => '%d',
			'series_position' => '%s',
			'pub_date'        => '%s',
			'pages'           => '%d',
			'synopsis'        => '%s',
			'goodreads_url'   => '%s',
			'buy_link'        => '%s'
		);
	}

	/**
	 * Get default column values.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'cover'           => null,
			'title'           => '',
			'index_title'     => '',
			'series_id'       => null,
			'series_position' => null,
			'pub_date'        => null,
			'pages'           => null,
			'synopsis'        => '',
			'goodreads_url'   => '',
			'buy_link'        => ''
		);
	}

	/**
	 * Add a Book
	 *
	 * @param array $data Book data.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int Book ID.
	 */
	public function add( $data = array() ) {

		$defaults = array();

		$args = wp_parse_args( $data, $defaults );

		$book = ( array_key_exists( 'ID', $args ) ) ? $this->get_book_by( 'ID', $args['ID'] ) : false;

		if ( $book ) {

			// Updating an existing book.
			$this->update( $book->ID, $args );

			return $book->ID;

		} else {

			// Adding a new book.
			return $this->insert( $args, 'book' );

		}

	}

	/**
	 * Delete a book.
	 *
	 * @param bool $id ID of the book to delete.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|int False on failure.
	 */
	public function delete( $id = false ) {

		if ( empty( $id ) ) {
			return false;
		}

		$book = $this->get_book_by( 'ID', $id );

		if ( $book->ID > 0 ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'ID' => $book->ID ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Delete Multiple Books by IDs
	 *
	 * @param array $ids Array of book IDs.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int|false Number of rows deleted or false if none.
	 */
	public function delete_by_ids( $ids ) {

		global $wpdb;

		if ( is_array( $ids ) ) {
			$ids = implode( ',', array_map( 'intval', $ids ) );
		} else {
			$ids = intval( $ids );
		}

		$results = $wpdb->query( "DELETE FROM  $this->table_name WHERE `ID` IN( {$ids} )" );

		return $results;

	}

	/**
	 * Check if a book exists.
	 *
	 * @param string $value Value of the column.
	 * @param string $field Which field to check.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function exists( $value = '', $field = 'ID' ) {

		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'ID', $field, $value );

	}

	/**
	 * Retrieves a single book, given an ID.
	 *
	 * @param int $book_id Book ID to fetch.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object|false Upon success, an object of the book. Upon failure, false.
	 */
	public function get_book( $book_id ) {

		return $this->get_book_by( 'ID', $book_id );

	}

	/**
	 * Retrieves a single book from the database.
	 *
	 * @param string $field The column to search.
	 * @param int    $value The value to check against the column.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return object|false Upon success, an object of the book. Upon failure, false.
	 */
	public function get_book_by( $field = 'ID', $value = 0 ) {

		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( $field == 'ID' || $field == 'series_id' ) {
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {

			case 'ID' :
				$db_field = 'ID';
				break;

			case 'title' :
				$db_field = 'title';
				$value    = wp_strip_all_tags( $value );
				break;

			case 'series' :
				$db_field = 'series_id';
				break;

			default :
				return false;

		}

		if ( ! $book = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			return false;

		}

		return wp_unslash( $book );

	}

	/**
	 * Retrieve books from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of objects.
	 */
	public function get_books( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'              => false,
			'number'          => 20,
			'offset'          => 0,
			'title'           => false,
			'author_id'       => false,
			'author_name'     => false,
			'series_name'     => false,
			'series_id'       => false,
			'series_position' => false,
			'pub_date'        => false,
			'rating'          => false,
			'orderby'         => 'ID',
			'order'           => 'DESC',
			'include_author'  => false,
			'include_rating'  => false
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Forcibly include rating.
		if ( false !== $args['rating'] ) {
			$args['include_rating'] = true;
		}

		$series_table = book_database()->series->table_name;

		// Join on series table to return the series name.
		$join .= " LEFT JOIN $series_table as series on books.series_id = series.ID";

		// Include author name.
		if ( $args['include_author'] ) {
			$term_relationship_table = book_database()->book_term_relationships->table_name;
			$term_table              = book_database()->book_terms->table_name;

			$join .= " LEFT JOIN {$term_relationship_table} as ar on books.ID = ar.book_id LEFT JOIN {$term_table} as author on (ar.term_id = author.term_id AND author.type = 'author')";
		}

		// Filter by specific author ID.
		if ( $args['author_id'] ) {
			$term_relationship_table = book_database()->book_term_relationships->table_name;
			$term_table              = book_database()->book_terms->table_name;

			$join .= " RIGHT JOIN $term_relationship_table as r on books.ID = r.book_id INNER JOIN $term_table as t on r.term_id = t.term_id";

			$where .= $wpdb->prepare( " AND t.type = %s AND t.term_id = %d", 'author', absint( $args['author_id'] ) );
		}

		// Filter by specific author name.
		if ( $args['author_name'] ) {
			$term_relationship_table = book_database()->book_term_relationships->table_name;
			$term_table              = book_database()->book_terms->table_name;

			$join .= " RIGHT JOIN $term_relationship_table as r on books.ID = r.book_id INNER JOIN $term_table as t on r.term_id = t.term_id";

			$where .= $wpdb->prepare( " AND t.type = %s AND t.name LIKE '%%%%" . '%s' . "%%%%'", 'author', wp_strip_all_tags( $args['author_name'] ) );
		}

		// Specific books.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Books with a specific title.
		if ( ! empty( $args['title'] ) ) {
			$where .= $wpdb->prepare( " AND `title` LIKE '%%%%" . '%s' . "%%%%' ", wp_strip_all_tags( $args['title'] ) );
		}

		// Specific books in a series.
		if ( ! empty( $args['series_id'] ) ) {
			if ( is_array( $args['series_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['series_id'] ) );
			} else {
				$ids = intval( $args['series_id'] );
			}
			$where .= " AND `series_id` IN( {$ids} ) ";
		}

		// Series name.
		if ( ! empty( $args['series_name'] ) ) {
			$where .= $wpdb->prepare( " AND series.name LIKE '%%%%" . '%s' . "%%%%'", wp_strip_all_tags( $args['series_name'] ) );
		}

		// Series in a certain position.
		if ( ! empty( $args['series_position'] ) ) {
			$where .= $wpdb->prepare( " AND `series_position` LIKE '%s'", wp_strip_all_tags( $args['series_position'] ) );
		}

		// Books published on a given date or in a range.
		if ( ! empty( $args['pub_date'] ) ) {

			if ( is_array( $args['pub_date'] ) ) {

				if ( ! empty( $args['pub_date']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $args['pub_date']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `pub_date` >= %s", $start );
				}

				if ( ! empty( $args['pub_date']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $args['pub_date']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `pub_date` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $args['pub_date'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $args['pub_date'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $args['pub_date'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( pub_date ) AND %d = MONTH ( pub_date ) AND %d = DAY ( pub_date )", $year, $month, $day );

			}

		}

		// Get average rating.
		if ( ! empty( $args['include_rating'] ) ) {
			$reading_table = book_database()->reading_list->table_name;
			$join .= " LEFT JOIN {$reading_table} as log on (books.ID = log.book_id AND log.rating IS NOT NULL)";

			if ( false !== $args['rating'] ) {
				$where .= $wpdb->prepare( " AND log.rating = %s", wp_strip_all_tags( $args['rating'] ) );
			}
		}

		switch ( $args['orderby'] ) {
			case 'series' :
				$orderby = 'series.name';
				break;

			case 'pub_date' :
				$orderby = 'books.pub_date';
				break;

			case 'rating' :
				if ( $args['include_rating'] ) {
					$orderby = 'avg_rating';
				} else {
					$orderby = 'books.ID';
				}
				break;

			default :
				$orderby = 'books.ID';
		}

		$order   = ( 'ASC' == strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		$cache_key = md5( 'bdb_books_' . serialize( $args ) );

		$books = wp_cache_get( $cache_key, 'books' );

		$select_this = 'books.*, series.name as series_name';
		if ( $args['author_id'] || $args['author_name'] ) {
			$select_this .= ', t.term_id as author_id, t.name as author_name';
		}
		if ( $args['include_author'] ) {
			$select_this .= ", GROUP_CONCAT(DISTINCT author.name SEPARATOR ',') as author_name, GROUP_CONCAT(DISTINCT author.term_id SEPARATOR ',') as author_id";
		}
		if ( $args['include_rating'] ) {
			$select_this .= ", ROUND(AVG(IF(log.rating = 'dnf', 0, log.rating)), 2) as avg_rating";
		}

		if ( $books === false ) {
			$query = $wpdb->prepare( "SELECT $select_this FROM  $this->table_name as books $join $where GROUP BY books.$this->primary_key ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$books = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $books, 'books', 3600 );
		}

		return wp_unslash( $books );

	}

	/**
	 * Count the total number of books in the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return int
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'              => false,
			'title'           => false,
			'author_id'       => false,
			'author_name'     => false,
			'series_name'     => false,
			'series_id'       => false,
			'series_position' => false,
			'pub_date'        => false,
			'rating'          => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$join  = '';
		$where = ' WHERE 1=1 ';

		// Specific books.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Books with a specific title.
		if ( ! empty( $args['title'] ) ) {
			$where .= $wpdb->prepare( " AND `title` LIKE '%%%%" . '%s' . "%%%%' ", wp_strip_all_tags( $args['title'] ) );
		}

		// Books in a specific series.
		if ( ! empty( $args['series_id'] ) ) {
			if ( is_array( $args['series_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['series_id'] ) );
			} else {
				$ids = intval( $args['series_id'] );
			}
			$where .= " AND `series_id` IN( {$ids} ) ";
		}

		// Series in a certain position.
		if ( ! empty( $args['series_position'] ) ) {
			$where .= $wpdb->prepare( " AND `series_position` LIKE '%s'", wp_strip_all_tags( $args['series_position'] ) );
		}

		// Author
		if ( ! empty( $args['author_id'] ) || ! empty( $args['author_name'] ) ) {

			$term_relationship_table = book_database()->book_term_relationships->table_name;
			$term_table              = book_database()->book_terms->table_name;

			$join .= " RIGHT JOIN $term_relationship_table as r on book.ID = r.book_id INNER JOIN $term_table as t on (r.term_id = t.term_id AND t.type = 'author')";

			if ( $args['author_id'] ) {
				$where .= $wpdb->prepare( " AND t.term_id = %d", absint( $args['author_id'] ) );
			}

			if ( $args['author_name'] ) {
				$where .= $wpdb->prepare( " AND t.name LIKE '%%%%" . '%s' . "%%%%'", wp_strip_all_tags( $args['author_name'] ) );
			}

		}

		// Series name.
		if ( ! empty( $args['series_name'] ) ) {
			$series_table = book_database()->series->table_name;

			$join .= " LEFT JOIN $series_table as series on book.series_id = series.ID";
			$where .= $wpdb->prepare( " AND series.name LIKE '%%%%" . '%s' . "%%%%'", wp_strip_all_tags( $args['series_name'] ) );
		}

		// Books published on a given date or in a range.
		if ( ! empty( $args['pub_date'] ) ) {

			if ( is_array( $args['pub_date'] ) ) {

				if ( ! empty( $args['pub_date']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $args['pub_date']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `pub_date` >= %s", $start );
				}

				if ( ! empty( $args['pub_date']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $args['pub_date']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `pub_date` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $args['pub_date'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $args['pub_date'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $args['pub_date'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( pub_date ) AND %d = MONTH ( pub_date ) AND %d = DAY ( pub_date )", $year, $month, $day );

			}

		}

		// Books with a specific rating.
		if ( false !== $args['rating'] ) {
			$reading_table = book_database()->reading_list->table_name;
			$join .= $wpdb->prepare( " INNER JOIN {$reading_table} as log on (book.ID = log.book_id AND log.rating = %s)", wp_strip_all_tags( $args['rating'] ) );
		}

		$cache_key = md5( 'bdb_books_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'books' );

		if ( $count === false ) {
			$query = "SELECT COUNT(book.ID) FROM " . $this->table_name . " as book {$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'books', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		cover bigint(20),
		title text NOT NULL,
		index_title text NOT NULL,
		series_id bigint(20),
		series_position float,
		pub_date datetime,
		pages bigint(20),
		synopsis longtext NOT NULL,
		goodreads_url text NOT NULL,
		buy_link text NOT NULL,
		PRIMARY KEY  (ID),
		INDEX series_id (series_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );

	}

}