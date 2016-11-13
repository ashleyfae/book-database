<?php

/**
 * Review Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_Review_Query
 *
 * @since 1.0.0
 */
class BDB_Review_Query {

	/**
	 * Current page number
	 *
	 * @var int
	 * @access protected
	 * @since  1.0.0
	 */
	protected $page;

	/**
	 * Offset
	 *
	 * Calculated from the page number.
	 *
	 * @var int
	 * @access protected
	 * @since  1.0.0
	 */
	protected $offset;

	/**
	 * Number of results per page
	 *
	 * @var int
	 * @access protected
	 * @since  1.0.0
	 */
	protected $per_page;

	protected $current_page;

	/**
	 * Total number of reviews
	 *
	 * @var int
	 * @access public
	 * @since  1.0.0
	 */
	public $total_reviews;

	/**
	 * Array of table names
	 *
	 * @var array
	 * @access protected
	 * @since  1.0.0
	 */
	protected $tables = array();

	/**
	 * Orderby
	 *
	 * @var string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $orderby;

	/**
	 * Order - ASC or DESC
	 *
	 * @var string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $order;

	/**
	 * Query vars
	 *
	 * @var array
	 * @access protected
	 * @since  1.0.0
	 */
	protected $query_vars;

	/**
	 * Results from the query
	 *
	 * @var array
	 * @access protected
	 * @since  1.0.0
	 */
	protected $reviews;

	/**
	 * BDB_Review_Query constructor.
	 *
	 * @param array $args Query arguments.
	 *
	 * @access public
	 * @since  1.0.0
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
			'book_title'  => false,
			'author_name' => false,
			'author_slug' => false,
			'series_name' => false,
			'rating'      => false,
			'genre'       => false,
			'publisher'   => false,
			'terms'       => array(),
			'date'        => false,
			'year'        => false,
			'month'       => false,
			'day'         => false,
			'pub_year'    => false,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'offset'      => false,
			'per_page'    => 20
		);
		$args     = wp_parse_args( $args, $defaults );

		// Set up query vars.
		$this->per_page   = $args['per_page'];
		$this->query_vars = $args;

		$this->current_page = ( isset( $_GET['bdbpage'] ) ) ? absint( $_GET['bdbpage'] ) : 1;

	}

	/**
	 * Parse Orderby
	 *
	 * @access protected
	 * @since  1.0.0
	 * @return void
	 */
	protected function parse_orderby() {
		$allowed_orderby = array(
			'title'           => 'book.index_title',
			'author'          => 'author.name',
			'date'            => 'date_added',
			'pub_date'        => 'book.pub_date',
			'series_position' => 'book.series_position',
			'pages'           => 'book.pages',
			'rating'          => 'rating'
		);

		$this->orderby = array_key_exists( $this->query_vars['orderby'], $allowed_orderby ) ? $allowed_orderby[ $this->query_vars['orderby'] ] : $allowed_orderby['title'];
		$this->order   = strtoupper( $this->query_vars['order'] ) == 'ASC' ? 'ASC' : 'DESC';
	}

	/**
	 * Parse Query Args
	 *
	 * Puts together query arguments based on $_GET and query vars.
	 *
	 * @access public
	 * @since  1.0.0
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
		if ( isset( $_GET['rating'] ) && 'any' != $_GET['rating'] ) {
			$this->query_vars['rating'] = wp_strip_all_tags( $_GET['rating'] );
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
		if ( isset( $_GET['review_year'] ) ) {
			$this->query_vars['year'] = absint( $_GET['review_year'] );
		}

		// Pub Year
		if ( isset( $_GET['pub_year'] ) ) {
			$this->query_vars['pub_year'] = absint( $_GET['pub_year'] );
		}

		// Orderby
		if ( isset( $_GET['orderby'] ) ) {
			$this->query_vars['orderby'] = wp_strip_all_tags( $_GET['orderby'] );
		}

		// Order
		if ( isset( $_GET['order'] ) ) {
			$this->query_vars['order'] = wp_strip_all_tags( $_GET['order'] );
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
	 * Query
	 *
	 * @access protected
	 * @since  1.0.0
	 * @return void
	 */
	public function query() {

		// Set up order & orderby.
		$this->parse_orderby();

		global $wpdb;

		$join  = '';
		$where = ' WHERE 1=1 ';

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
		if ( $this->query_vars['rating'] ) {
			$where .= $wpdb->prepare( " AND rating LIKE '" . '%s' . "'", sanitize_text_field( wp_strip_all_tags( $this->query_vars['rating'] ) ) );
		}

		// Review date parameters
		if ( ! empty( $this->query_vars['date'] ) ) {

			if ( is_array( $this->query_vars['date'] ) ) {

				if ( ! empty( $this->query_vars['date']['start'] ) ) {
					$start = date( 'Y-m-d 00:00:00', strtotime( $this->query_vars['date']['start'] ) );
					$where .= " AND `date_added` >= '{$start}'";
				}

				if ( ! empty( $this->query_vars['date']['end'] ) ) {
					$end = date( 'Y-m-d 23:59:59', strtotime( $this->query_vars['date']['end'] ) );
					$where .= " AND `date_added` <= '{$end}'";
				}

			} else {

				$year  = date( 'Y', strtotime( $this->query_vars['date'] ) );
				$month = date( 'm', strtotime( $this->query_vars['date'] ) );
				$day   = date( 'd', strtotime( $this->query_vars['date'] ) );
				$where .= " AND $year = YEAR ( date_added ) AND $month = MONTH ( date_added ) AND $day = DAY ( date_added )";

			}

		}

		// Review date -- year
		if ( $this->query_vars['year'] ) {
			$where .= $wpdb->prepare( " AND %d = YEAR ( date_added )", absint( $this->query_vars['year'] ) );
		}
		// Review date -- month
		if ( $this->query_vars['month'] ) {
			$where .= $wpdb->prepare( " AND %d = MONTH ( date_added )", absint( $this->query_vars['month'] ) );
		}
		// Review date -- day
		if ( $this->query_vars['day'] ) {
			$where .= $wpdb->prepare( " AND %d = DAY ( date_added )", absint( $this->query_vars['day'] ) );
		}

		// Pub date -- year
		if ( $this->query_vars['pub_year'] ) {
			$where .= $wpdb->prepare( " AND %d = YEAR ( book.pub_date )", absint( $this->query_vars['pub_year'] ) );
		}

		// Filter by genre
		if ( $this->query_vars['genre'] ) {
			$inner_join = is_numeric( $this->query_vars['genre'] ) ? "INNER JOIN {$this->tables['terms']} terms ON r.term_id = terms.term_id AND terms.type = %s AND terms.term_id = %d" : "INNER JOIN {$this->tables['terms']} terms ON r.term_id = terms.term_id AND terms.type = %s AND terms.slug = %s";
			$where .= $wpdb->prepare( "AND book.ID IN (
										SELECT DISTINCT (book.ID) FROM {$this->tables['books']} book
										INNER JOIN {$this->tables['relationships']} r ON book.ID = r.book_id
										{$inner_join}
									)", 'genre', sanitize_text_field( $this->query_vars['genre'] ) );
		}

		// Filter by publisher
		if ( $this->query_vars['publisher'] ) {
			$inner_join = is_numeric( $this->query_vars['publisher'] ) ? "INNER JOIN {$this->tables['terms']} terms ON r.term_id = terms.term_id AND terms.type = %s AND terms.term_id = %d" : "INNER JOIN {$this->tables['terms']} terms ON r.term_id = terms.term_id AND terms.type = %s AND terms.slug = %s";
			$where .= $wpdb->prepare( "AND book.ID IN (
										SELECT DISTINCT (book.ID) FROM {$this->tables['books']} book
										INNER JOIN {$this->tables['relationships']} r ON book.ID = r.book_id
										{$inner_join}
									)", 'publisher', sanitize_text_field( $this->query_vars['publisher'] ) );
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

				$where .= $wpdb->prepare( "AND book.ID IN (
										SELECT DISTINCT (book.ID) FROM {$this->tables['books']} book
										INNER JOIN {$this->tables['relationships']} r ON book.ID = r.book_id
										INNER JOIN {$this->tables['terms']} terms ON r.term_id = terms.term_id AND terms.type = %s AND terms.term_id = %d
									)", sanitize_text_field( $tax ), absint( $term_id ) );
			}
		}

		// Tweak order by rating.
		if ( 'rating' == $this->orderby ) {
			$this->orderby = $this->orderby . " * 1";
		}

		$query = "SELECT DISTINCT review.ID, review.post_id, review.url, review.rating, review.date_added,
				        book.ID as book_id, book.cover as book_cover_id, book.title as book_title, book.index_title as book_index_title, book.series_position,
				        series.ID as series_id, series.name as series_name,
				        author.term_id as author_id, author.name as author_name
				FROM {$this->tables['reviews']} as review
				INNER JOIN {$this->tables['books']} as book ON review.book_id = book.ID
				LEFT JOIN {$this->tables['series']} as series ON book.series_id = series.ID
				LEFT JOIN {$this->tables['relationships']} as r ON book.ID = r.book_id
				INNER JOIN {$this->tables['terms']} as author ON (r.term_id = author.term_id AND author.type = 'author')
				{$join}
				{$where}
				ORDER BY {$this->orderby}
				{$this->order}";

		// Get the total number of results.
		$total_query         = "SELECT COUNT(1) FROM ({$query}) AS combined_table";
		$this->total_reviews = $wpdb->get_var( $total_query );

		// Add pagination parameters.
		$offset     = ( false !== $this->query_vars['offset'] ) ? $this->query_vars['offset'] : ( $this->current_page * $this->per_page ) - $this->per_page;
		$pagination = $wpdb->prepare( " LIMIT %d, %d", $offset, $this->per_page );

		// Get the final results.
		$reviews = $wpdb->get_results( $query . $pagination );

		$this->reviews = stripslashes_deep( $reviews );

	}

	/**
	 * Whether or not we have reviews to cycle through.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool
	 */
	public function have_reviews() {
		return ( is_array( $this->reviews ) && count( $this->reviews ) );
	}

	/**
	 * Get Reviews
	 *
	 * Sets up BDB_Book and BDB_Review objects for everything.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array|false
	 */
	public function get_reviews() {

		if ( ! $this->have_reviews() ) {
			return false;
		}

		$final = array();

		foreach ( $this->reviews as $entry ) {
			// Set up book class.
			$book_tmp                  = new stdClass();
			$book_tmp->ID              = $entry->book_id;
			$book_tmp->cover           = $entry->book_cover_id;
			$book_tmp->title           = $entry->book_title;
			$book_tmp->series_id       = $entry->series_id;
			$book_tmp->series_position = $entry->series_position;
			$book                      = new BDB_Book( $book_tmp );

			// Set up review class.
			$review_tmp             = new stdClass();
			$review_tmp->ID         = $entry->ID;
			$review_tmp->book_id    = $entry->book_id;
			$review_tmp->post_id    = $entry->post_id;
			$review_tmp->url        = $entry->url;
			$review_tmp->rating     = $entry->rating;
			$review_tmp->date_added = $entry->date_added;
			$review                 = new BDB_Review( $review_tmp );

			$final[] = array(
				'book'   => $book,
				'review' => $review
			);
		}

		return $final;

	}

	public function get_pagination() {

		return paginate_links( array(
			'base'      => add_query_arg( 'bdbpage', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $this->total_reviews / $this->per_page ),
			'current'   => $this->current_page
		) );

	}

}