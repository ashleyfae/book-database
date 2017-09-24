<?php

/**
 * Owned Editions DB Class
 *
 * This class is for interacting with the books owned database table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BDB_DB_Owned_Editions extends BDB_DB {

	/**
	 * BDB_DB_Owned_Editions constructor.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'bdb_owned_editions';
		$this->primary_key = 'ID';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'ID'            => '%d',
			'book_id'       => '%d',
			'isbn'          => '%s',
			'format'        => '%s',
			'date_acquired' => '%s',
			'signed'        => '%d'
		);
	}

	/**
	 * Get default column values.
	 *
	 * @access public
	 * @since  1.0
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'book_id'       => 0,
			'isbn'          => '',
			'format'        => '',
			'date_acquired' => null,
			'signed'        => null
		);
	}

	/**
	 * Add an owned book
	 *
	 * @param array $data Book data.
	 *
	 * @access public
	 * @since  1.0
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
			return $this->insert( $args, 'book_owned' );

		}

	}

	/**
	 * Delete an owned book
	 *
	 * @param bool $id ID of the book to delete.
	 *
	 * @access public
	 * @since  1.0
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
	 * Delete multiple owned books by IDs
	 *
	 * @param array $ids Array of book IDs.
	 *
	 * @access public
	 * @since  1.0
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
	 * Check if an owned exists.
	 *
	 * @param string $value Value of the column.
	 * @param string $field Which field to check.
	 *
	 * @access public
	 * @since  1.0
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
	 * Retrieves a single owned book record, given an ID.
	 *
	 * @param int $book_id Book ID to fetch.
	 *
	 * @access public
	 * @since  1.0
	 * @return object|false Upon success, an object of the book. Upon failure, false.
	 */
	public function get_book( $book_id ) {

		return $this->get_book_by( 'ID', $book_id );

	}

	/**
	 * Retrieves a single owned book record from the database.
	 *
	 * @param string $field The column to search.
	 * @param int    $value The value to check against the column.
	 *
	 * @access public
	 * @since  1.0
	 * @return object|false Upon success, an object of the book. Upon failure, false.
	 */
	public function get_book_by( $field = 'ID', $value = 0 ) {

		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( $field == 'ID' || $field == 'book_id' ) {
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
				// sanitization already occurred above
				break;

			case 'book_id' :
				$db_field = 'book_id';
				// sanitization already occurred above
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
	 * Retrieve owned books from the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of objects.
	 */
	public function get_books( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'            => false,
			'number'        => 20,
			'offset'        => 0,
			'book_id'       => false,
			'isbn'          => false,
			'format'        => false,
			'date_acquired' => false,
			'signed'        => false,
			'orderby'       => 'ID',
			'order'         => 'DESC'
		);

		$args = wp_parse_args( $args, $defaults );

		// Big ass number to get them all.
		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$join  = '';
		$where = ' WHERE 1=1 ';

		$books_table = book_database()->books->table_name;

		// Specific owned books.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Specific books.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
			$join .= " INNER JOIN  $books_table as books on books.ID = ob.book_id ";
		}

		// Books with a specific ISBN.
		if ( ! empty( $args['isbn'] ) ) {
			$where .= $wpdb->prepare( " AND `isbn` = %s ", sanitize_text_field( $args['isbn'] ) );
		}

		// Books with a specific format.
		if ( ! empty( $args['format'] ) ) {
			$where .= $wpdb->prepare( " AND `format` = %s ", sanitize_text_field( $args['format'] ) );
		}

		// Books acquired within a date range
		if ( ! empty( $args['date_acquired'] ) ) {

			if ( is_array( $args['date_acquired'] ) ) {

				if ( ! empty( $args['date_acquired']['start'] ) ) {
					$start = get_gmt_from_date( sanitize_text_field( $args['date_acquired']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_acquired` >= %s", $start );
				}

				if ( ! empty( $args['date_acquired']['end'] ) ) {
					$end   = get_gmt_from_date( sanitize_text_field( $args['date_acquired']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_acquired` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( sanitize_text_field( $args['date_acquired'] ), 'Y' );
				$month = get_gmt_from_date( sanitize_text_field( $args['date_acquired'] ), 'm' );
				$day   = get_gmt_from_date( sanitize_text_field( $args['date_acquired'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_acquired ) AND %d = MONTH ( date_acquired ) AND %d = DAY ( date_acquired )", $year, $month, $day );

			}

		}

		// Get signed books only.
		if ( ! empty( $args['signed'] ) ) {
			$where .= " AND `signed` IS NOT NULL ";
		}

		switch ( $args['orderby'] ) {
			case 'date_acquired' :
				$orderby = 'ob.date_acquired';
				break;

			default :
				$orderby = 'ob.ID';
		}

		$order   = ( 'ASC' == strtoupper( $args['order'] ) ) ? 'ASC' : 'DESC';
		$orderby = esc_sql( $orderby );
		$order   = esc_sql( $order );

		$cache_key = md5( 'bdb_owned_editions_' . serialize( $args ) );

		$books = wp_cache_get( $cache_key, 'owned_editions' );

		if ( $books === false ) {
			$query = $wpdb->prepare( "SELECT ob.* FROM  $this->table_name as ob $join $where GROUP BY ob.$this->primary_key ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
			$books = $wpdb->get_results( $query );
			wp_cache_set( $cache_key, $books, 'owned_editions', 3600 );
		}

		return wp_unslash( $books );

	}

	/**
	 * Count the total number of books in the database.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0
	 * @return int
	 */
	public function count( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'ID'              => false,
			'book_id'       => false,
			'isbn'          => false,
			'format'        => false,
			'date_acquired' => false,
			'signed'        => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$join  = '';
		$where = ' WHERE 1=1 ';

		$books_table = book_database()->books->table_name;

		// Specific owned books.
		if ( ! empty( $args['ID'] ) ) {
			if ( is_array( $args['ID'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['ID'] ) );
			} else {
				$ids = intval( $args['ID'] );
			}
			$where .= " AND `ID` IN( {$ids} ) ";
		}

		// Specific books.
		if ( ! empty( $args['book_id'] ) ) {
			if ( is_array( $args['book_id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['book_id'] ) );
			} else {
				$ids = intval( $args['book_id'] );
			}
			$where .= " AND `book_id` IN( {$ids} ) ";
			$join .= " INNER JOIN  $books_table as books on books.ID = ob.book_id ";
		}

		// Books with a specific ISBN.
		if ( ! empty( $args['isbn'] ) ) {
			$where .= $wpdb->prepare( " AND `isbn` = %s ", sanitize_text_field( $args['isbn'] ) );
		}

		// Books with a specific format.
		if ( ! empty( $args['format'] ) ) {
			$where .= $wpdb->prepare( " AND `format` = %s ", sanitize_text_field( $args['format'] ) );
		}

		// Books acquired within a date range
		if ( ! empty( $args['date_acquired'] ) ) {

			if ( is_array( $args['date_acquired'] ) ) {

				if ( ! empty( $args['date_acquired']['start'] ) ) {
					$start = get_gmt_from_date( sanitize_text_field( $args['date_acquired']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_acquired` >= %s", $start );
				}

				if ( ! empty( $args['date_acquired']['end'] ) ) {
					$end   = get_gmt_from_date( sanitize_text_field( $args['date_acquired']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_acquired` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( sanitize_text_field( $args['date_acquired'] ), 'Y' );
				$month = get_gmt_from_date( sanitize_text_field( $args['date_acquired'] ), 'm' );
				$day   = get_gmt_from_date( sanitize_text_field( $args['date_acquired'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_acquired ) AND %d = MONTH ( date_acquired ) AND %d = DAY ( date_acquired )", $year, $month, $day );

			}

		}

		// Get signed books only.
		if ( ! empty( $args['signed'] ) ) {
			$where .= " AND `signed` IS NOT NULL ";
		}

		$cache_key = md5( 'bdb_owned_editions_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'owned_editions' );

		if ( $count === false ) {
			$query = "SELECT COUNT(ob.ID) FROM " . $this->table_name . " as ob {$join} {$where};";
			$count = $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $count, 'owned_editions', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		ID BIGINT(20) NOT NULL AUTO_INCREMENT,
		book_id BIGINT(20) NOT NULL,
		isbn varchar(13) NOT NULL,
		format varchar(200) NOT NULL,
		date_acquired DATETIME,
		signed INT(1),
		PRIMARY KEY  (ID),
		INDEX book_id (book_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version, false );

	}

}