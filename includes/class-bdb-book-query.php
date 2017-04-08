<?php

/**
 * Book Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_Book_Query
 *
 * @since 1.3.0
 */
class BDB_Book_Query {

	/**
	 * Current page number
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $page;

	/**
	 * Offset
	 *
	 * Calculated from the page number.
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $offset;

	/**
	 * Number of results per page
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $per_page;

	/**
	 * Current page number
	 *
	 * @var int
	 * @access protected
	 * @since  1.0
	 */
	protected $current_page;

	/**
	 * Total number of books
	 *
	 * @var int
	 * @access public
	 * @since  1.0
	 */
	public $total_books;

	/**
	 * Array of table names
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $tables = array();

	/**
	 * Orderby
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $orderby;

	/**
	 * Order - ASC or DESC
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $order;

	/**
	 * Query vars
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $query_vars;

	/**
	 * Whether to join on the reading log table
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $table_log_join = false;

	/**
	 * Whether to join on reviews table
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $table_reviews_join = false;

	/**
	 * Whether or not to join on the authors table, which is
	 * actually just the terms table but we treat it differently.
	 * This is probably stupid and should be changed at some point.
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $table_authors_join = false;

	/**
	 * Whether to join on terms table
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $table_terms_join = false;

	/**
	 * Whether to join on the series table
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $table_series_join = false;

	/**
	 * Array of columns to select from the database
	 *
	 * @var array
	 * @access public
	 * @since  1.0
	 */
	public $select = array();

	/**
	 * What to group the query results by
	 *
	 * @var string
	 * @access public
	 * @since  1.0
	 */
	public $group_by;

	/**
	 * Whether or not to return books only
	 *
	 * @var bool
	 * @access public
	 * @since  1.0
	 */
	public $return_books_only = true;

	/**
	 * Primary type of query ('books' or 'reviews'). This determines whether we select
	 * distinct book IDs or distinct review IDs (the latter of which may result in
	 * the same book appearing multiple times with different reviews).
	 *
	 * @var string
	 * @access protected
	 * @since  1.0
	 */
	protected $query_type = 'books';

	/**
	 * Results from the query
	 *
	 * @var array
	 * @access protected
	 * @since  1.0
	 */
	protected $books;

	/**
	 * BDB_Book_Query constructor.
	 *
	 * @param array  $args       Query arguments.
	 * @param string $query_type Primary query type ('books' or 'reviews').
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct( $args = array(), $query_type = 'books' ) {

		// Set up table names.
		$this->tables['reviews']       = book_database()->reviews->table_name;
		$this->tables['books']         = book_database()->books->table_name;
		$this->tables['series']        = book_database()->series->table_name;
		$this->tables['terms']         = book_database()->book_terms->table_name;
		$this->tables['relationships'] = book_database()->book_term_relationships->table_name;
		$this->tables['log']           = book_database()->reading_list->table_name;

		// Default args.
		$defaults = array(
			'ids'                 => false,// get specific book IDs
			'book_title'          => false, // filter by book title
			'author_name'         => false, // specific author name
			'author_slug'         => false, // author slug
			'series_name'         => false, // specific series name
			'series_id'           => false, // specific series ID
			'rating'              => false, // specific rating
			'terms'               => array(), // specific term values
			'review_date'         => false, // specific review date (value or array)
			'review_year'         => false, // review year (number)
			'review_month'        => false, // review month (number)
			'review_day'          => false, // review day (number)
			'pub_date'            => false, // book publication date (value or array)
			'pub_year'            => false, // book publication year (number)
			'pub_month'           => false, // book publication month (number)
			'pub_day'             => false, // book publication day (number)
			'orderby'             => 'date', // order results by
			'order'               => 'DESC', // order
			'offset'              => false,
			'hide_future_reviews' => false, // whether to hide reviews not yet published
			'per_page'            => 20,
			'nopaging'            => false,
			'return_books_only'   => true
		);
		$args     = wp_parse_args( $args, $defaults );

		// Set up query vars.
		$this->per_page          = $args['per_page'];
		$this->query_vars        = $args;
		$this->query_type        = ( 'reviews' == $query_type ) ? 'reviews' : 'books';
		$this->current_page      = ( isset( $_GET['bdbpage'] ) ) ? absint( $_GET['bdbpage'] ) : 1;
		$this->return_books_only = $args['return_books_only'];

		if ( 'reviews' == $this->query_type ) {
			$primary_select = 'DISTINCT review.ID as review_id, book.ID as book_id';
			$this->group_by = 'review.ID';
		} else {
			$primary_select = 'DISTINCT book.ID as book_id';
			$this->group_by = 'book.ID';
		}

		$this->select = array(
			$primary_select,
			'book.cover as book_cover_id',
			'book.title as book_title',
			'series_position',
			'pub_date',
			'goodreads_url'
		);

	}

	/**
	 * Parse Orderby
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function parse_orderby() {
		$allowed_orderby = array(
			'title'           => 'book.index_title',
			'author'          => 'author.name',
			'date'            => 'date_published',
			'date_written'    => 'date_written',
			'date_started'    => 'log.date_started',
			'date_finished'   => 'log.date_finished',
			'pub_date'        => 'book.pub_date',
			'series_position' => 'book.series_position',
			'pages'           => 'book.pages',
			'rating'          => 'rating',
			'id'              => 'book.ID'
		);

		$this->orderby = array_key_exists( $this->query_vars['orderby'], $allowed_orderby ) ? $allowed_orderby[ $this->query_vars['orderby'] ] : $allowed_orderby['title'];
		$this->order   = strtoupper( $this->query_vars['order'] ) == 'ASC' ? 'ASC' : 'DESC';
	}

	/**
	 * Add new column to select in query
	 *
	 * @param string $column ID of the column to add.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function add_select( $column ) {
		if ( ! in_array( $column, $this->select ) ) {
			$this->select[] = $column;
		}
	}

	/**
	 * Remove column ID from the selection
	 *
	 * @param string $column ID of the column to remove.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function remove_select( $column ) {
		$key = array_search( $column, $this->select );
		if ( false !== $key ) {
			unset( $this->select[ $key ] );
		}
	}

	/**
	 * Parse Query Args
	 *
	 * Puts together query arguments based on $_GET and query vars.
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of query vars.
	 */
	public function parse_query_args() {

		// Book title
		if ( isset( $_GET['title'] ) ) {
			$this->query_vars['book_title'] = wp_strip_all_tags( $_GET['title'] );
		}

		// Author
		if ( isset( $_GET['author'] ) ) {
			$this->query_vars['author_name'] = $author_name = wp_strip_all_tags( $_GET['author'] );
		}

		// Series
		if ( isset( $_GET['series'] ) ) {
			$this->query_vars['series_name'] = $series_name = wp_strip_all_tags( $_GET['series'] );
		}

		// Rating
		if ( isset( $_GET['rating'] ) && 'any' != $_GET['rating'] && 'all' != $_GET['rating'] ) {
			$allowed_ratings = bdb_get_available_ratings();

			if ( array_key_exists( $_GET['rating'], $allowed_ratings ) ) {
				$this->query_vars['rating'] = wp_strip_all_tags( $_GET['rating'] );
			}
		}

		// Genre
		if ( isset( $_GET['genre'] ) && 'all' != $_GET['genre'] ) {
			$this->query_vars['terms']['genre'] = absint( $_GET['genre'] );
		}

		// Publisher
		if ( isset( $_GET['publisher'] ) && 'all' != $_GET['publisher'] ) {
			$this->query_vars['terms']['publisher'] = absint( $_GET['publisher'] );
		}

		// Review Year
		if ( isset( $_GET['review_year'] ) && 'all' != $_GET['review_year'] ) {
			$this->query_vars['review_year'] = absint( $_GET['review_year'] );
		}

		// Pub Year
		if ( isset( $_GET['pub_year'] ) ) {
			$this->query_vars['pub_year'] = absint( $_GET['pub_year'] );
		}

		// Orderby
		if ( isset( $_GET['orderby'] ) ) {
			$orderby = wp_strip_all_tags( $_GET['orderby'] );

			if ( ! array_key_exists( $orderby, bdb_get_allowed_orderby() ) ) {
				$orderby = 'date';
			}

			$this->query_vars['orderby'] = wp_slash( $orderby );
		}

		// Order
		if ( isset( $_GET['order'] ) ) {
			$this->query_vars['order'] = ( 'ASC' == $_GET['order'] ) ? 'ASC' : 'DESC';
		}

		/*
		 * Look in WP_Query
		 */
		global $wp_query;

		if ( array_key_exists( 'book_tax', $wp_query->query_vars ) && array_key_exists( 'book_term', $wp_query->query_vars ) ) {

			if ( 'series' == $wp_query->query_vars['book_tax'] ) {

				$series_obj = bdb_get_series( array(
					'slug'   => sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_term'] ) ),
					'fields' => 'names'
				) );

				if ( $series_obj ) {
					$this->query_vars['series_name'] = $series_obj;
					$this->query_vars['orderby']     = 'pub_date';
					$this->query_vars['order']       = 'ASC';
				}

			} elseif ( 'rating' == $wp_query->query_vars['book_tax'] ) {

				$allowed_ratings = bdb_get_available_ratings();

				if ( array_key_exists( $wp_query->query_vars['book_term'], $allowed_ratings ) ) {
					$this->query_vars['rating'] = wp_strip_all_tags( $wp_query->query_vars['book_term'] );
				}

			} else {

				$type    = sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_tax'] ) );
				$term_id = bdb_get_term( array(
					'type'   => $type,
					'slug'   => sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_term'] ) ),
					'fields' => 'ids'
				) );

				if ( $term_id ) {
					$this->query_vars['terms'][ $type ] = $term_id;
				}

			}

		}

		return $this->query_vars;

	}

	/**
	 * Setup table joins
	 *
	 * @access protected
	 * @since  1.0
	 * @return void
	 */
	protected function setup_joins() {

		// Join on reading logs table.
		if ( 'rating' == $this->orderby || ! empty( $this->query_vars['rating'] ) || in_array( $this->orderby, array(
				'date_started',
				'date_finished'
			) )
		) {
			$this->table_log_join = true;
		}

		// Join on reviews table.
		if ( 'reviews' == $this->query_type || $this->query_vars['review_date'] || $this->query_vars['hide_future_reviews'] || in_array( $this->orderby, array(
				'date_written',
				'date_published'
			) )
		) {
			$this->table_reviews_join = true;
		}

		// Join on author table.
		if ( $this->query_vars['author_name'] || $this->query_vars['author_slug'] ) {
			$this->table_authors_join = true;
		}

		// Join on series table.
		if ( $this->query_vars['series_name'] || $this->query_vars['series_id'] ) {
			$this->table_series_join = true;
		}

		// @todo

	}

	/**
	 * Query
	 *
	 * @access protected
	 * @since  1.3.0
	 * @return void
	 */
	public function query() {

		// Set up order & orderby.
		$this->parse_orderby();

		// Set up table joins.
		$this->setup_joins();

		global $wpdb;

		$join  = '';
		$where = ' WHERE 1=1 ';
		$group = '';

		/*
		 * Table joins
		 */

		// Reading log table.
		if ( $this->table_log_join ) {
			$join .= " LEFT JOIN {$this->tables['log']} AS log ON (book.ID = log.book_id AND log.rating IS NOT NULL)";
		}

		// Review table.
		if ( $this->table_reviews_join ) {
			$reviews_join_dir = 'LEFT';

			if ( 'reviews' == $this->query_type ) {
				$reviews_join_dir = 'INNER';
			}

			$join .= " {$reviews_join_dir} JOIN {$this->tables['reviews']} AS review ON (book.ID = review.book_id)";
		}

		// Authors table.
		if ( $this->table_authors_join ) {
			$join .= " INNER JOIN {$this->tables['relationships']} as ar ON book.ID = ar.book_ID INNER JOIN {$this->tables['terms']} as author ON (ar.term_id = author.term_id AND author.type = 'author') ";
		}

		// Series table.
		if ( $this->table_series_join ) {
			$join .= " INNER JOIN {$this->tables['series']} as series on book.series_id = series.ID ";
		}

		/*
		 * Where clauses
		 */

		// If reviews only, only show published ones.
		if ( 'reviews' == $this->query_type ) {
			$current = get_gmt_from_date( 'now', 'Y-m-d H:i:s' );
			$where   .= $wpdb->prepare( " AND `date_published` <= %s", $current );
		}

		// Specific books.
		if ( $this->query_vars['ids'] ) {
			if ( is_array( $this->query_vars['ids'] ) ) {
				$ids = implode( ',', array_map( 'intval', $this->query_vars['ids'] ) );
			} else {
				$ids = intval( $this->query_vars['ids'] );
			}
			$where .= " AND book.ID IN( {$ids} ) ";
		}

		// Filter by book title.
		if ( $this->query_vars['book_title'] ) {
			$where .= $wpdb->prepare( " AND book.title LIKE '%%%%" . '%s' . "%%%%'", sanitize_text_field( wp_strip_all_tags( $this->query_vars['book_title'] ) ) );
		}

		// Filter by author name.
		if ( $this->query_vars['author_name'] ) {
			$where .= $wpdb->prepare( " AND author.name LIKE '%%%%" . '%s' . "%%%%'", sanitize_text_field( wp_strip_all_tags( $this->query_vars['author_name'] ) ) );
		}
		// Filter by author slug.
		if ( $this->query_vars['author_slug'] ) {
			$where .= $wpdb->prepare( " AND author.slug = %s", sanitize_text_field( wp_strip_all_tags( $this->query_vars['author_slug'] ) ) );
		}

		// Filter by series name.
		if ( $this->query_vars['series_name'] ) {
			$where .= $wpdb->prepare( " AND series.name LIKE '%%%%" . '%s' . "%%%%' ", sanitize_text_field( wp_strip_all_tags( $this->query_vars['series_name'] ) ) );
		}

		// Filter by series ID.
		if ( $this->query_vars['series_id'] ) {
			$where .= $wpdb->prepare( " AND series.ID = %d ", absint( $this->query_vars['series_id'] ) );
		}

		// Filter by rating.
		if ( $this->query_vars['rating'] && 'any' != $this->query_vars['rating'] && 'all' != $this->query_vars['rating'] ) {
			$where .= $wpdb->prepare( " AND log.rating LIKE '" . '%s' . "'", sanitize_text_field( wp_strip_all_tags( $this->query_vars['rating'] ) ) );
		}

		// Book pub date parameters
		if ( ! empty( $this->query_vars['pub_date'] ) ) {

			if ( is_array( $this->query_vars['pub_date'] ) ) {

				if ( ! empty( $this->query_vars['pub_date']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['pub_date']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `pub_date` >= %s", $start );
				}

				if ( ! empty( $this->query_vars['pub_date']['end'] ) ) {
					$end   = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['pub_date']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `pub_date` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['pub_date'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['pub_date'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['pub_date'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( pub_date ) AND %d = MONTH ( pub_date ) AND %d = DAY ( pub_date )", $year, $month, $day );

			}

		}

		// Review date parameters
		if ( ! empty( $this->query_vars['review_date'] ) ) {

			if ( is_array( $this->query_vars['review_date'] ) ) {

				if ( ! empty( $this->query_vars['review_date']['start'] ) ) {
					$start = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date']['start'] ), 'Y-m-d 00:00:00' );
					$where .= $wpdb->prepare( " AND `date_published` >= %s", $start );
				}

				if ( ! empty( $this->query_vars['review_date']['end'] ) ) {
					$end   = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_published` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_published ) AND %d = MONTH ( date_published ) AND %d = DAY ( date_published )", $year, $month, $day );

			}

		}

		// Review date -- year
		if ( $this->query_vars['review_year'] ) {
			$where .= $wpdb->prepare( " AND %d = YEAR ( date_written )", absint( $this->query_vars['review_year'] ) );
		}
		// Review date -- month
		if ( $this->query_vars['review_month'] ) {
			$where .= $wpdb->prepare( " AND %d = MONTH ( date_written )", absint( $this->query_vars['review_month'] ) );
		}
		// Review date -- day
		if ( $this->query_vars['review_day'] ) {
			$where .= $wpdb->prepare( " AND %d = DAY ( date_written )", absint( $this->query_vars['review_day'] ) );
		}

		// Book publication date -- year
		if ( $this->query_vars['pub_year'] ) {
			$where .= $wpdb->prepare( " AND %d = YEAR ( pub_date )", absint( $this->query_vars['pub_year'] ) );
		}
		// Book publication date -- month
		if ( $this->query_vars['pub_month'] ) {
			$where .= $wpdb->prepare( " AND %d = MONTH ( pub_date )", absint( $this->query_vars['pub_month'] ) );
		}
		// Book publication date -- day
		if ( $this->query_vars['pub_day'] ) {
			$where .= $wpdb->prepare( " AND %d = DAY ( pub_date )", absint( $this->query_vars['pub_day'] ) );
		}

		// Only show reviews that have already been published.
		if ( $this->query_vars['hide_future_reviews'] ) {
			$current = get_gmt_from_date( 'now', 'Y-m-d H:i:s' );
			$where   .= $wpdb->prepare( " AND `date_published` <= %s", $current );
		}

		// Filter by misc terms.
		if ( is_array( $this->query_vars['terms'] ) && count( $this->query_vars['terms'] ) ) {
			$allowed_terms = array_keys( bdb_get_taxonomies( true ) );

			foreach ( $this->query_vars['terms'] as $tax => $term_id ) {
				if ( ! in_array( $tax, $allowed_terms ) ) {
					continue;
				}

				if ( ! is_numeric( $term_id ) ) {
					continue;
				}

				$where .= $wpdb->prepare(
					"AND book.ID IN (
						SELECT DISTINCT (book.ID) FROM {$this->tables['books']} book
						INNER JOIN {$this->tables['relationships']} r ON book.ID = r.book_id
						INNER JOIN {$this->tables['terms']} terms ON r.term_id = terms.term_id AND terms.type = %s AND terms.term_id = %d
					)",
					sanitize_text_field( $tax ),
					absint( $term_id )
				);
			}
		}

		/*
		 * Set up extra select params.
		 */
		if ( 'rating' == $this->orderby || ( 'books' == $this->query_type && $this->table_log_join ) ) {
			$this->add_select( 'ROUND(AVG(IF(log.rating = \'dnf\', 0, log.rating)), 2) as rating' );
		}
		if ( $this->table_reviews_join ) {
			$this->add_select( 'review.post_id' );
			$this->add_select( 'review.url' );
		}
		if ( $this->table_series_join ) {
			$this->add_select( 'book.series_id' );
			$this->add_select( 'series.name as series_name' );
		}
		if ( $this->table_authors_join ) {
			$this->add_select( 'author.term_id as author_id' );
			$this->add_select( 'GROUP_CONCAT(author.name SEPARATOR \', \') as author_name' );
		}
		if ( 'reviews' == $this->query_type && $this->table_log_join ) {
			$this->add_select( 'log.rating as rating' );
		}
		$select = implode( ', ', array_unique( $this->select ) );

		// Tweak order by rating.
		if ( 'rating' == $this->orderby ) {
			$this->orderby = $this->orderby . " * 1";
		}

		// Grouping
		if ( ! empty( $this->group_by ) ) {
			$group = 'GROUP BY ' . esc_sql( $this->group_by );
		}

		$query = "SELECT $select
			FROM {$this->tables['books']} AS book
			{$join}
			{$where}
			{$group} 
			ORDER BY {$this->orderby} 
			{$this->order}";

		// Only get total number of results if `nopaging` is `false` and we haven't already gotten all of them.
		if ( ! $this->query_vars['nopaging'] || - 1 == $this->per_page ) {
			$total_cache_key = md5( 'bdb_book_query_count_' . serialize( $this->query_vars ) );
			$total           = wp_cache_get( $total_cache_key, 'book_query' );

			if ( false === $total ) {
				$total_query       = "SELECT COUNT(1) FROM ({$query}) AS combined_table";
				$this->total_books = $wpdb->get_var( $total_query );

				wp_cache_set( $total_cache_key, $this->total_books, 'book_query', 3600 );
			} else {
				$this->total_books = $total;
			}
		}

		// Add pagination parameters to query.
		if ( $this->per_page > 0 ) {
			$offset     = ( false !== $this->query_vars['offset'] ) ? $this->query_vars['offset'] : ( $this->current_page * $this->per_page ) - $this->per_page;
			$pagination = $wpdb->prepare( " LIMIT %d, %d", $offset, $this->per_page );
		} else {
			$pagination = $wpdb->prepare( " LIMIT %d", 999999999999 );
		}

		// Get the final results.
		$cache_key = md5( 'bdb_book_query_' . serialize( $this->query_vars ) );
		$books     = wp_cache_get( $cache_key, 'book_query' );

		if ( false === $books ) {
			$books = $wpdb->get_results( $query . $pagination );
			wp_cache_set( $cache_key, $books, 'book_query', 3600 );
		}

		if ( $this->query_vars['nopaging'] || - 1 == $this->per_page ) {
			$this->total_books = count( $books );
		}

		$this->books = wp_unslash( $books );

	}

	/**
	 * Whether or not we have books to cycle through
	 *
	 * @access public
	 * @since  1.0
	 * @return bool
	 */
	public function have_books() {
		return ( is_array( $this->books ) && count( $this->books ) );
	}

	/**
	 * Get Books
	 *
	 * Sets up BDB_Book and BDB_Review objects for everything.
	 *
	 * @access public
	 * @since  1.0
	 * @return array|false
	 */
	public function get_books() {

		if ( ! $this->have_books() ) {
			return false;
		}

		$final = array();

		foreach ( $this->books as $entry ) {
			// Set up book class.
			$book_tmp                  = new stdClass();
			$book_tmp->ID              = $entry->book_id;
			$book_tmp->cover           = $entry->book_cover_id;
			$book_tmp->title           = $entry->book_title;
			$book_tmp->series_id       = isset( $entry->series_id ) ? $entry->series_id : false;
			$book_tmp->series_position = isset( $entry->series_position ) ? $entry->series_position : false;
			$book_tmp->goodreads_url   = isset( $entry->goodreads_url ) ? $entry->goodreads_url : false;
			$book                      = new BDB_Book( $book_tmp );

			// Set up review class.
			$review_tmp                 = new stdClass();
			$review_tmp->ID             = isset( $entry->review_id ) ? $entry->review_id : false;
			$review_tmp->book_id        = $entry->book_id;
			$review_tmp->post_id        = isset( $entry->post_id ) ? $entry->post_id : false;
			$review_tmp->url            = isset( $entry->url ) ? $entry->url : false;
			$review_tmp->rating         = isset( $entry->rating ) ? $entry->rating : false;
			$review_tmp->date_written   = isset( $entry->date_written ) ? $entry->date_written : false;
			$review_tmp->date_published = isset( $entry->date_published ) ? $entry->date_published : false;
			$review                     = new BDB_Review( $review_tmp );

			if ( $this->return_books_only ) {
				$final[] = $book;
			} else {
				$final[] = array(
					'book'   => $book,
					'review' => $review
				);
			}
		}

		return $final;

	}

	/**
	 * Get pagination links
	 *
	 * @access public
	 * @since  1.0
	 * @return string
	 */
	public function get_pagination() {

		return paginate_links( array(
			'base'      => add_query_arg( 'bdbpage', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $this->total_books / $this->per_page ),
			'current'   => $this->current_page
		) );

	}

}