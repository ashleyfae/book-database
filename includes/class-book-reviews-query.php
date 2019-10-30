<?php
/**
 * Book Reviews Query
 *
 * Used in the `[book-reviews]` shortcode.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Reviews_Query
 * @package Book_Database
 */
class Book_Reviews_Query {

	/**
	 * @var int Current page number.
	 */
	protected $current_page = 1;

	/**
	 * @var int Number of results per page.
	 */
	protected $per_page = 20;

	/**
	 * @var array Query arguments.
	 */
	protected $args = array(
		'orderby' => 'review.date_published',
		'order'   => 'DESC'
	);

	/**
	 * @var int Total number of results.
	 */
	public $total_results = 0;

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

		$this->args['number'] = $args['per-page'] ?? 20;
		$this->args['offset'] = ( $this->current_page * $this->args['number'] ) - $this->args['number'];
		$this->per_page       = $this->args['number'];

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
				'field'    => 'name',
				'value'    => sanitize_text_field( wp_strip_all_tags( urldecode( $_GET['author'] ) ) ),
				'operator' => 'LIKE'
			);
		}

		// Series
		if ( ! empty( $_GET['series'] ) ) {
			$this->args['series_query'][] = array(
				'field'    => 'name',
				'value'    => sanitize_text_field( wp_strip_all_tags( urldecode( $_GET['series'] ) ) ),
				'operator' => 'LIKE'
			);
		}

		// Rating
		if ( isset( $_GET['rating'] ) && is_numeric( $_GET['rating'] ) ) {
			$this->args['reading_log_query'][] = array(
				'field' => 'rating',
				'value' => floatval( $_GET['rating'] )
			);
		}

		// Taxonomies
		foreach ( get_book_taxonomies( array( 'fields' => 'slug' ) ) as $taxonomy_slug ) {
			if ( ! empty( $_GET[ $taxonomy_slug ] ) && 'any' !== $_GET[ $taxonomy_slug ] ) {
				$this->args['tax_query'][] = array(
					'taxonomy' => sanitize_text_field( $taxonomy_slug ),
					'field'    => 'id',
					'terms'    => absint( $_GET[ $taxonomy_slug ] )
				);
			}
		}

		// Review Year
		if ( ! empty( $_GET['review_year'] ) && is_numeric( $_GET['review_year'] ) ) {
			$this->args['review_query'][] = array(
				'field' => 'date_published',
				'value' => array(
					'year' => absint( $_GET['review_year'] )
				)
			);
		}

		// Hide Future Reviews
		if ( ! empty( $args['hide-future'] ) ) {
			$this->args['review_query'][] = array(
				'field' => 'date_published',
				'value' => array(
					'before' => current_time( 'mysql' )
				)
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
			$this->args['orderby'] = wp_strip_all_tags( $_GET['orderby'] );
		}

		// Order
		if ( ! empty( $_GET['order'] ) ) {
			$this->args['order'] = 'ASC' === $_GET['order'] ? 'ASC' : 'DESC';
		}

		/**
		 * Look in WP_Query
		 */
		global $wp_query;

		if ( ! empty( $wp_query->query_vars['book_tax'] ) && ! empty( $wp_query->query_vars['book_term'] ) ) {

			switch ( $wp_query->query_vars['book_tax'] ) {

				case 'series' :
					$this->args['series_query'] = array(
						array(
							'field' => 'slug',
							'value' => array( sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_term'] ) ) ),
						)
					);
					$this->args['orderby']      = 'book.pub_date';
					$this->args['order']        = 'ASC';
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
							'value' => array( sanitize_text_field( wp_strip_all_tags( $wp_query->query_vars['book_term'] ) ) ),
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
	 * Get the reviews
	 *
	 * @return object[]
	 */
	public function get_results() {

		$query   = new Reviews_Query();
		$reviews = $query->get_reviews( $this->args );

		$count_args          = $this->args;
		$count_args['count'] = true;
		$this->total_results = $query->get_reviews( $count_args );

		return $reviews;

	}

	/**
	 * Get pagination
	 *
	 * @return string
	 */
	public function get_pagination() {
		return paginate_links( array(
			'base'      => add_query_arg( 'bdbpage', '%#%' ),
			'format'    => '',
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => ceil( $this->total_results / $this->per_page ),
			'current'   => $this->current_page
		) );
	}

}