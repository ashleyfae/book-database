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
	 * Number of results.
	 *
	 * @var int
	 * @access protected
	 * @since  1.3.0
	 */
	protected $number;

	/**
	 * Array of table names
	 *
	 * @var array
	 * @access protected
	 * @since  1.3.0
	 */
	protected $tables = array();

	/**
	 * Orderby
	 *
	 * @var string
	 * @access protected
	 * @since  1.3.0
	 */
	protected $orderby;

	/**
	 * Order - ASC or DESC
	 *
	 * @var string
	 * @access protected
	 * @since  1.3.0
	 */
	protected $order;

	/**
	 * Query vars
	 *
	 * @var array
	 * @access protected
	 * @since  1.3.0
	 */
	protected $query_vars;

	/**
	 * Join on log table
	 *
	 * @var bool
	 * @access protected
	 * @since  1.3.0
	 */
	protected $table_log_join = false;

	/**
	 * Join on reviews table
	 *
	 * Will either be `false`, `LEFT`, or `INNER`
	 *
	 * @var bool|string
	 * @access protected
	 * @since  1.3.0
	 */
	protected $table_reviews_join = false;

	/**
	 * Join on terms table
	 *
	 * @var bool
	 * @access protected
	 * @since  1.3.0
	 */
	protected $table_terms_join = false;

	/**
	 * Results from the query
	 *
	 * @var array
	 * @access protected
	 * @since  1.3.0
	 */
	protected $books;

	/**
	 * BDB_Book_Query constructor.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.3.0
	 * @return void
	 */
	public function __construct( $args = array() ) {

		// Set up table names.
		$this->tables['reviews']       = book_database()->reviews->table_name;
		$this->tables['books']         = book_database()->books->table_name;
		$this->tables['series']        = book_database()->series->table_name;
		$this->tables['terms']         = book_database()->book_terms->table_name;
		$this->tables['relationships'] = book_database()->book_term_relationships->table_name;

		// Default args.
		$defaults = array(
			'book_title'          => false,
			'author_name'         => false,
			'author_slug'         => false,
			'series_name'         => false,
			'rating'              => false,
			'terms'               => array(),
			'review_date'         => false,
			'year'                => false, // review written year
			'month'               => false, // review written month
			'day'                 => false, // review written day
			'pub_date'            => false,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'offset'              => false,
			'hide_future'         => false,
			'show_ratings'        => false,
			'show_review_link'    => false,
			'show_goodreads_link' => false,
			'reviews_only'        => false,
			'number'              => 20
		);
		$args     = wp_parse_args( $args, $defaults );

		// Set up query vars.
		$this->number     = absint( $args['number'] );
		$this->query_vars = $args;

	}

	/**
	 * Parse Orderby
	 *
	 * @access protected
	 * @since  1.3.0
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
			'rating'          => 'rating'
		);

		$this->orderby = array_key_exists( $this->query_vars['orderby'], $allowed_orderby ) ? $allowed_orderby[ $this->query_vars['orderby'] ] : $allowed_orderby['title'];
		$this->order   = strtoupper( $this->query_vars['order'] ) == 'ASC' ? 'ASC' : 'DESC';
	}

	protected function setup_joins() {

		// Ratings - join on logs.
		if ( 'rating' == $this->orderby || true == $this->query_vars['show_ratings'] ) {
			$this->table_log_join = true;
		}

		// Reviews -- required.
		if ( true == $this->query_vars['reviews_only'] ) {
			$this->table_reviews_join = 'INNER';
		}

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

		// Always join on reading log to get rating.
		$reading_table = book_database()->reading_list->table_name;
		$join .= " LEFT JOIN {$reading_table} as log on log.book_id = book.ID";

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
			$where .= $wpdb->prepare( " AND series.name LIKE '%%%%" . '%s' . "%%%%'", sanitize_text_field( wp_strip_all_tags( $this->query_vars['series_name'] ) ) );
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
					$end = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['pub_date']['end'] ), 'Y-m-d 23:59:59' );
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
					$where .= $wpdb->prepare( " AND `date_written` >= %s", $start );
				}

				if ( ! empty( $this->query_vars['review_date']['end'] ) ) {
					$end = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date']['end'] ), 'Y-m-d 23:59:59' );
					$where .= $wpdb->prepare( " AND `date_written` <= %s", $end );
				}

			} else {

				$year  = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date'] ), 'Y' );
				$month = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date'] ), 'm' );
				$day   = get_gmt_from_date( wp_strip_all_tags( $this->query_vars['review_date'] ), 'd' );
				$where .= $wpdb->prepare( " AND %d = YEAR ( date_written ) AND %d = MONTH ( date_written ) AND %d = DAY ( date_written )", $year, $month, $day );

			}

		}

		// Review date -- year
		if ( $this->query_vars['year'] ) {
			$where .= $wpdb->prepare( " AND %d = YEAR ( date_written )", absint( $this->query_vars['year'] ) );
		}
		// Review date -- month
		if ( $this->query_vars['month'] ) {
			$where .= $wpdb->prepare( " AND %d = MONTH ( date_written )", absint( $this->query_vars['month'] ) );
		}
		// Review date -- day
		if ( $this->query_vars['day'] ) {
			$where .= $wpdb->prepare( " AND %d = DAY ( date_written )", absint( $this->query_vars['day'] ) );
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

		// Tweak order by rating.
		if ( 'rating' == $this->orderby ) {
			$this->orderby = $this->orderby . " * 1";
		}

		// Set up extra select params.
		$select = '';

		if ( 'rating' == $this->orderby || true == $this->query_vars['show_ratings'] ) {
			$select .= ', log.rating as rating';
		}

		$query = $wpdb->prepare(
			"SELECT DISTINCT book.ID as book_id, book.cover as book_cover_id, book.title as book_title $select
			FROM {$this->tables['books']}
			{$join}
			{$where}
			GROUP BY book.ID 
			ORDER BY {$this->orderby} 
			{$this->order}
			LIMIT %d",
			$this->number
		);

		$books = $wpdb->get_results( $query );

		$this->books = wp_unslash( $books );

	}

}