<?php
/**
 * Reviews Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\BerlinDB\Database\Queries\Tax;

/**
 * Class Reviews_Query
 * @package Book_Database
 */
class Reviews_Query extends BerlinDB\Database\Query {

	/**
	 * Name of the table to query
	 *
	 * @var string
	 */
	protected $table_name = 'reviews';

	/**
	 * String used to alias the database table in MySQL statements
	 *
	 * @var string
	 */
	protected $table_alias = 'review';

	/**
	 * Name of class used to set up the database schema
	 *
	 * @var string
	 */
	protected $table_schema = '\\Book_Database\\Reviews_Schema';

	/**
	 * Name for a single item
	 *
	 * @var string
	 */
	protected $item_name = 'review';

	/**
	 * Plural version for a group of items
	 *
	 * @var string
	 */
	protected $item_name_plural = 'reviews';

	/**
	 * Class name to turn IDs into these objects
	 *
	 * @var string
	 */
	protected $item_shape = '\\Book_Database\\Review';

	/**
	 * Group to cache queries and queried items to
	 *
	 * @var string
	 */
	protected $cache_group = 'reviews';

	/**
	 * Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	/**
	 * Get the column in this table to join with the taxonomy terms column.
	 *
	 * @return string
	 */
	protected function get_tax_query_join_column_name() {
		return 'book_id';
	}

	/**
	 * Get the column in this table to join with the book column.
	 *
	 * @return string
	 */
	protected function get_author_query_join_column_name() {
		return 'book_id';
	}

	/**
	 * Query for reviews
	 *
	 * @param array $args
	 *
	 * @return object[]|int
	 */
	public function get_reviews( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'author_query'      => array(),
			'book_query'        => array(),
			'series_query'      => array(),
			'reading_log_query' => array(),
			'review_query'      => array(),
			'edition_query'     => array(),
			'tax_query'         => array(),
			'orderby'           => 'review.id',
			'order'             => 'DESC',
			'number'            => 20,
			'offset'            => 0,
			'count'             => false
		) );

		$select = $join = $where = array();

		$clause_engine = new Where_Clause();

		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_author   = book_database()->get_table( 'authors' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();
		$tbl_ed       = book_database()->get_table( 'editions' )->get_table_name();
		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_reviews  = book_database()->get_table( 'reviews' )->get_table_name();
		$tbl_series   = book_database()->get_table( 'series' )->get_table_name();

		// Select
		$select = array(
			'review.*',
			'book.id as book_id',
			'book.cover_id as book_cover_id',
			'book.title as book_title',
			'book.pub_date as book_pub_date',
			'book.series_position as series_position',
			"GROUP_CONCAT( DISTINCT author.id SEPARATOR ',' ) as author_id",
			"GROUP_CONCAT( DISTINCT author.name SEPARATOR ',' ) as author_name",
			'series.id as series_id',
			'series.name as series_name',
			'log.date_started as date_started_reading',
			'log.date_finished as date_finished_reading',
			'log.percentage_complete as percentage_complete',
			'log.rating as rating',
		);

		// Book Join
		$join['book_query'] = "LEFT JOIN {$tbl_books} AS book ON review.book_id = book.id";

		// Author Join
		$join['author_query'] = "LEFT JOIN {$tbl_author_r} AS ar ON review.book_id = ar.book_id LEFT JOIN {$tbl_author} AS author ON ar.author_id = author.id";

		// Series Join
		$join['series_query'] = "LEFT JOIN {$tbl_series} AS series ON book.series_id = series.id";

		// Reading Log Join
		$join['reading_log_query'] = "LEFT JOIN {$tbl_log} AS log ON log.review_id = review.id";

		/**
		 * Where
		 */

		// Author query
		if ( ! empty( $args['author_query'] ) ) {
			$clause_engine->set_table_query( new Authors_Query() );
			$clause_engine->set_args( $args['author_query'] );
			$where = array_merge( $where, $clause_engine->get_clauses() );
		}

		// Book query
		if ( ! empty( $args['book_query'] ) ) {
			$clause_engine->set_table_query( new Books_Query() );
			$clause_engine->set_args( $args['book_query'] );
			$where = array_merge( $where, $clause_engine->get_clauses() );
		}

		// Edition query
		if ( ! empty( $args['edition_query'] ) ) {
			$join['edition_query'] = "INNER JOIN {$tbl_ed} AS ed ON (review.book_id = ed.book_id)";
			$clause_engine->set_table_query( new Editions_Query() );
			$clause_engine->set_args( $args['edition_query'] );
			$where = array_merge( $where, $clause_engine->get_clauses() );
		}

		// Reading log query
		if ( ! empty( $args['reading_log_query'] ) ) {
			$clause_engine->set_table_query( new Reading_Logs_Query() );
			$clause_engine->set_args( $args['reading_log_query'] );
			$where = array_merge( $where, $clause_engine->get_clauses() );
		}

		// Review query
		if ( ! empty( $args['review_query'] ) ) {
			$clause_engine->set_table_query( $this );
			$clause_engine->set_args( $args['review_query'] );
			$where = array_merge( $where, $clause_engine->get_clauses() );
		}

		// Series query
		if ( ! empty( $args['series_query'] ) ) {
			$clause_engine->set_table_query( new Series_Query() );
			$clause_engine->set_args( $args['series_query'] );
			$where = array_merge( $where, $clause_engine->get_clauses() );
		}

		// Tax query
		if ( ! empty( $args['tax_query'] ) ) {
			$tax_query          = new Tax( $args['tax_query'] );
			$clauses            = $tax_query->get_sql( $this->table_alias, 'book_id' );
			$join['tax_query']  = $clauses['join'];
			$where['tax_query'] = preg_replace( '/^\s*AND\s*/', '', $clauses['where'] );
		}

		/**
		 * Format and query
		 */
		$select = implode( ', ', $select );
		$join   = implode( ' ', $join );
		$where  = ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';

		/**
		 * Validate the orderby / order
		 */
		$orderby = $this->validate_orderby( $args['orderby'], $args );
		$order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$group_by = 'GROUP BY review.id';

		// Override select if we're counting.
		if ( ! empty( $args['count'] ) ) {
			$select   = 'COUNT( DISTINCT review.id )';
			$group_by = '';
		}

		if ( ! empty( $args['count'] ) ) {
			$query = "SELECT {$select} FROM {$tbl_reviews} AS review {$join} {$where}";

			$reviews = $this->get_db()->get_var( $query );

			return absint( $reviews );
		}

		$query = $this->get_db()->prepare( "SELECT {$select} FROM {$tbl_reviews} AS review {$join} {$where} {$group_by} ORDER BY $orderby $order LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) );

		$reviews = $this->get_db()->get_results( $query );

		return wp_unslash( $reviews );

	}

	/**
	 * Validate the orderby
	 *
	 * @param string $orderby Desired orderby.
	 * @param array  $args    Query arguments.
	 *
	 * @return string
	 */
	protected function validate_orderby( $orderby, $args = array() ) {

		$valid_orderbys = array(
			'review.id',
			'review.book_id',
			'review.user_id',
			'review.post_id',
			'review.date_written',
			'review.date_published',
			'review.date_created',
			'review.date_modified',
			'author.id',
			'author.name',
			'author.slug',
			'book.id',
			'book.title',
			'book.index_title',
			'book.series_id',
			'book.series_position',
			'book.pub_date',
			'book.pages',
			'book.date_created',
			'book.date_modified',
			'series.id',
			'series.name',
			'series.slug',
			'series.number_books',
			'series.date_created',
			'log.id',
			'log.review_id',
			'log.user_id',
			'log.date_started',
			'log.date_finished',
			'log.percentage_complete',
			'log.rating'
		);

		return in_array( $orderby, $valid_orderbys ) ? $orderby : 'review.id';

	}

}