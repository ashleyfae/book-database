<?php
/**
 * Book Reviews Query
 *
 * Used in the `[book_reviews]` shortcode.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use \Book_Database\BerlinDB\Database\Queries\Author as Author_Query;
use \Book_Database\BerlinDB\Database\Queries\Book as Book_Query;
use \Book_Database\BerlinDB\Database\Queries\Date as Date_Query;
use \Book_Database\BerlinDB\Database\Queries\Reading_Log as Reading_Log_Query;
use \Book_Database\BerlinDB\Database\Queries\Tax as Tax_Query;

/**
 * Class Book_Reviews_Query
 * @package Book_Database
 */
class Book_Reviews_Query {

	/**
	 * @var string `wp_bdb_reviews` table alias.
	 */
	protected $table_alias = 'review';

	/**
	 * @var int Current page number.
	 */
	protected $current_page = 1;

	/**
	 * @var array Whitelist of "orderby" columns.
	 */
	protected $orderby_whitelist = array(
		'author.name', // Book author
		'book.title', // Book title
		'log.date_finished', // Date finished reading
		'review.date_published', // Date review was written
		'book.pages', // Number of pages in the book
		'book.pub_date', // Book publication date
		'log.rating' // Rating
	);

	/**
	 * @var array Query arguments.
	 */
	protected $args = array(
		'orderby' => 'date_published',
		'order'   => 'DESC'
	);

	/**
	 * @var int Total number of results.
	 */
	protected $total_results = 0;

	/**
	 * @var array Query clauses.
	 */
	protected $clauses = array(
		'join'  => array(),
		'where' => array()
	);

	/**
	 * Book_Reviews_Query constructor.
	 *
	 * @param array $args Shortcode attributes.
	 */
	public function __construct( $args = array() ) {
		$this->current_page = isset( $_GET['bdbpage'] ) ? absint( $_GET['bdbpage'] ) : 1;
		$this->parse_args( $args );
	}

	/**
	 * Parse the query args from a combination of shortcode attributes and query args.
	 *
	 * @param array $args Shortcode attributes.
	 */
	protected function parse_args( $args = array() ) {

		$this->args['number'] = $args['per_page'] ?? 20;
		$this->args['offset'] = ( $this->current_page * $this->args['number'] ) - $this->args['number'];

		// Book title.
		if ( ! empty( $_GET['title'] ) ) {
			$this->args['book_query'][] = array(
				'field'    => 'title',
				'value'    => sanitize_text_field( wp_strip_all_tags( urldecode( $_GET['title'] ) ) ),
				'operator' => 'LIKE'
			);
		}

		// Author
		if ( ! empty( $_GET['author'] ) ) {
			$this->args['author_query'][] = array(
				'field' => 'search',
				'terms' => array( sanitize_text_field( wp_strip_all_tags( urldecode( $_GET['author'] ) ) ) ),
			);
		}

		// Series
		if ( ! empty( $_GET['series'] ) ) {
			// @todo
		}

		// Rating
		if ( ! empty( $_GET['rating'] ) && is_numeric( $_GET['rating'] ) ) {
			$this->args['reading_log_query'][] = array(
				'field' => 'rating',
				'value' => floatval( $_GET['rating'] )
			);
		}

		// Taxonomies
		foreach ( get_book_taxonomies( array( 'fields' => 'slug' ) ) as $taxonomy_slug ) {
			if ( ! empty( $_GET[ $taxonomy_slug ] ) ) {
				$this->args['tax_query'][] = array(
					'taxonomy' => sanitize_text_field( $taxonomy_slug ),
					'field'    => 'id',
					'terms'    => absint( $_GET[ $taxonomy_slug ] )
				);
			}
		}

		// Review Year
		if ( ! empty( $_GET['review_year'] ) && is_numeric( $_GET['review_year'] ) ) {
			$this->args['date_written_query'] = array(
				'year' => absint( $_GET['review_year'] )
			);
		}

		// Hide Future Reviews
		if ( ! empty( $args['hide_future'] ) ) {
			$this->args['date_published_query'] = array(
				'before' => current_time( 'mysql' )
			);
		}

		// Book Publication Year
		if ( ! empty( $_GET['pub_year'] ) ) {
			$this->args['book_query'][] = array(
				'field' => 'pub_date',
				'value' => array(
					'year' => absint( $_GET['pub_year'] )
				)
			);
		}

		// Orderby
		if ( ! empty( $_GET['orderby'] ) ) {
			$this->args['orderby'] = $this->sanitize_orderby( $_GET['orderby'] );
		}

		/**
		 * Look in WP_Query
		 */
		global $wp_query;

		if ( ! empty( $wp_query->query_vars['book_tax'] ) && ! empty( $wp_query->query_vars['book_term'] ) ) {

			switch ( $wp_query->query_vars['book_tax'] ) {

				case 'series' :
					// @todo actual series query
					$this->args['orderby'] = 'book.pub_date';
					$this->args['order']   = 'ASC';
					break;

				case 'rating' :
					if ( array_key_exists( $wp_query->query_vars['book_term'], get_available_ratings() ) ) {
						$this->args['reading_log_query'] = array(
							array(
								'field' => 'rating',
								'value' => floatval( $wp_query->query_vars['book_term'] )
							)
						);
					}
					break;

				case 'author' :
					$this->args['author_query'] = array(
						array(
							'field' => 'slug',
							'terms' => array( sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_term'] ) ) ),
						)
					);
					break;

				default :
					$this->args['tax_query'][] = array(
						'taxonomy' => sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_tax'] ) ),
						'field'    => 'slug',
						'terms'    => sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_term'] ) )
					);

			}

		}

	}

	/**
	 * Sanitizes the desired "orderby" value
	 *
	 * @param string $orderby
	 *
	 * @return string Column to order by.
	 */
	protected function sanitize_orderby( $orderby ) {

		$orderby = wp_strip_all_tags( $orderby );

		return in_array( $orderby, $this->orderby_whitelist ) ? $orderby : 'review.date_published';

	}

	/**
	 * Parse the `where` clause and build an array of joins and where conditions.
	 *
	 * @return array
	 */
	protected function parse_where() {

		global $wpdb;

		$join = $where = array();
		$and  = '/^\s*AND\s*/';

		// Author Query
		if ( ! empty( $this->args['author_query'] ) ) {
			$author_query         = new Author_Query( $this->args['author_query'] );
			$author_query_clauses = $author_query->get_sql( $this->table_alias, 'book_id' );

			if ( ! empty( $author_query_clauses ) ) {
				$join['author_query']  = $author_query_clauses['join'];
				$where['author_query'] = preg_replace( $and, '', $author_query_clauses['where'] );
			}
		}

		// Book Query
		if ( ! empty( $this->args['book_query'] ) ) {
			$book_query         = new Book_Query( $this->args['book_query'], $this->table_alias, 'book_id' );
			$book_query_clauses = $book_query->get_sql();

			if ( ! empty( $book_query_clauses ) ) {
				$join['book_query']  = $book_query_clauses['join'];
				$where['book_query'] = preg_replace( $and, '', $book_query_clauses['where'] );
			}
		}

		// Reading Log Query
		if ( ! empty( $this->args['reading_log_query'] ) ) {
			$reading_log_query         = new Reading_Log_Query( $this->args['reading_log_query'], $this->table_alias, 'book_id' );
			$reading_log_query_clauses = $reading_log_query->get_sql();

			if ( ! empty( $author_query_clauses ) ) {
				$join['reading_log_query']  = $reading_log_query_clauses['join'];
				$where['reading_log_query'] = preg_replace( $and, '', $reading_log_query_clauses['where'] );
			}
		}

		// Tax Query
		if ( ! empty( $this->args['tax_query'] ) ) {
			$tax_query         = new Tax_Query( $this->args['tax_query'] );
			$tax_query_clauses = $tax_query->get_sql( $this->table_alias, 'book_id' );

			if ( ! empty( $tax_query_clauses ) ) {
				$join['tax_query']  = $tax_query_clauses['join'];
				$where['tax_query'] = preg_replace( $and, '', $tax_query_clauses['where'] );
			}
		}

		/**
		 * Normal columns
		 */

		// Filter by user ID
		if ( ! empty( $this->args['user_id'] ) ) {
			$where['user_id'] = $wpdb->prepare( "`user_id` = %d", absint( $this->args['user_id'] ) );
		}

		// Date Written Query
		if ( ! empty( $this->args['date_written_query'] ) ) {
			$date_written_query  = new Date_Query( $this->args['date_written_query'], 'review.date_written' );
			$date_written_clause = $date_written_query->get_sql();

			if ( ! empty( $date_written_clause ) ) {
				$where['date_written_query'] = preg_replace( $and, '', $date_written_clause );
			}
		}

		// Date Published Query
		if ( ! empty( $this->args['date_published_query'] ) ) {
			$date_published_query  = new Date_Query( $this->args['date_published_query'], 'review.date_published' );
			$date_published_clause = $date_published_query->get_sql();

			if ( ! empty( $date_published_clause ) ) {
				$where['date_published_query'] = preg_replace( $and, '', $date_published_clause );
			}
		}

		/**
		 * Ensure we always join with the books table.
		 */
		if ( empty( $join['book_query'] ) ) {
			//$book_table         = book_database()->get_table( 'books' )->get_table_name();
			//$join['book_query'] = " INNER JOIN {$book_table} book ON book.id = review.book_id ";
		}

		$this->clauses['join']  = $join;
		$this->clauses['where'] = $where;

		return $this->clauses;

	}

	/**
	 * Get the reviews
	 *
	 * @return Review[]
	 */
	public function get_reviews() {
		$this->total_results = count_reviews( $this->args );
		var_dump( get_reviews( $this->args ) );

		return;

		global $wpdb;

		$review_table = book_database()->get_table( 'reviews' )->get_table_name();

		$clauses = $this->parse_where();
		$join    = implode( ' ', $clauses['join'] );
		$where   = implode( ' AND ', $clauses['where'] );

		if ( ! empty( $where ) ) {
			$where = ' AND ' . $where;
		}
		var_dump( $clauses );

		$offset = '';
		if ( $this->args['offset'] > 0 ) {
			$offset = $wpdb->prepare( "OFFSET %D", $this->args['offset'] );
		}

		$query = $wpdb->prepare(
			"SELECT review.*, book.* FROM {$review_table} review {$join} {$where} ORDER BY {$this->args['orderby']} {$this->args['order']} LIMIT %d {$offset}",
			$this->args['number']
		);

		print_r( $query );

		$reviews = $wpdb->get_results( $query );

		var_dump( $reviews );

	}

}