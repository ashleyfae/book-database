<?php
/**
 * Books Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\BerlinDB\Database\Query;

/**
 * Class Books_Query
 * @package Book_Database
 */
class Books_Query extends BerlinDB\Database\Query {

	/**
	 * Name of the table to query
	 *
	 * @var string
	 */
	protected $table_name = 'books';

	/**
	 * String used to alias the database table in MySQL statements
	 *
	 * @var string
	 */
	protected $table_alias = 'b';

	/**
	 * Name of class used to set up the database schema
	 *
	 * @var string
	 */
	protected $table_schema = '\\Book_Database\\Books_Schema';

	/**
	 * Name for a single item
	 *
	 * @var string
	 */
	protected $item_name = 'book';

	/**
	 * Plural version for a group of items
	 *
	 * @var string
	 */
	protected $item_name_plural = 'books';

	/**
	 * Class name to turn IDs into these objects
	 *
	 * @var string
	 */
	protected $item_shape = '\\Book_Database\\Book';

	/**
	 * Group to cache queries and queried items to
	 *
	 * @var string
	 */
	protected $cache_group = 'books';

	/**
	 * Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	public function get_books( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'title'             => '',
			'author_query'      => array(),
			'series_id'         => false,
			'series_query'      => array(),
			'series_position'   => false,
			'pub_date_query'    => array(),
			'reading_log_query' => array(),
			'edition_query'     => array(),
			'tax_query'         => array(),
			'isbn'              => false,
			'format'            => false,
			'orderby'           => 'id',
			'order'             => 'DESC',
			'include_author'    => true,
			'include_rating'    => true,
			'number'            => 20,
			'offset'            => 0
		) );

		$select = $join = $where = array();

		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_author   = book_database()->get_table( 'authors' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();
		$tbl_terms    = book_database()->get_table( 'book_terms' )->get_table_name();
		$tbl_term_r   = book_database()->get_table( 'book_term_relationships' )->get_table_name();
		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_series   = book_database()->get_table( 'series' )->get_table_name();

		// Select
		$select = array(
			'book.*',
			"GROUP_CONCAT( DISTINCT author.id SEPARATOR ',' ) as author_id",
			"GROUP_CONCAT( DISTINCT author.name SEPARATOR ',' ) as author_name",
			'series.id as series_id',
			'series.name as series_name'
		);

		// Author Join
		$join['author_query'] = "LEFT JOIN {$tbl_author_r} AS ar ON book.id = ar.book_id LEFT JOIN {$tbl_author} AS author ON ar.author_id = author.id";

		// Series Join
		$join['series_query'] = "LEFT JOIN {$tbl_series} AS series ON book.series_id = series.id";

		// Average Rating
		if ( ! empty( $args['include_rating'] ) ) {
			$join['average_rating_select'] = "LEFT JOIN {$tbl_log} AS avg_rating ON (book.id = avg_rating.book_id AND avg_rating.rating IS NOT NULL)";
			$select[]                      = 'ROUND( AVG( avg_rating.rating ), 2 ) as avg_rating';
		}

		/**
		 * Where
		 */

		/**
		 * Format and query
		 */
		$select = implode( ', ', $select );
		$join   = implode( ' ', $join );
		$where  = ! empty( $hwere ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		$orderby = $args['orderby'];
		$order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$query = $this->get_db()->prepare( "SELECT {$select} FROM {$tbl_books} AS book $join $where GROUP BY book.id ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );
		error_log($query);
		$books = $this->get_db()->get_results( $query );

		return wp_unslash( $books );

	}

}