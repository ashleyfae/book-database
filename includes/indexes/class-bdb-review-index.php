<?php

/**
 * Base Review Index Class
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
 * Class BDB_Review_Index
 *
 * @since 1.0.0
 */
class BDB_Review_Index {

	/**
	 * Shortcode Attributes
	 *
	 * @var array
	 * @access protected
	 * @since  1.0.0
	 */
	protected $atts;

	/**
	 * Single review template
	 *
	 * @var string
	 * @access protected
	 * @since  1.0.0
	 */
	protected $template;

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
	 * BDB_Review_Index constructor.
	 *
	 * @param array  $atts     Shortcode attributes.
	 * @param string $template Template for individual reviews.
	 *
	 * @access public
	 * @since  1.0.0
	 */
	public function __construct( $atts = array(), $template = '' ) {

		$this->atts     = $atts;
		$this->template = $template ? $template : '[link_start][title] by [author] [series][link_end]</a>';

		$this->tables['reviews']       = book_database()->reviews->table_name;
		$this->tables['books']         = book_database()->books->table_name;
		$this->tables['series']        = book_database()->series->table_name;
		$this->tables['terms']         = book_database()->book_terms->table_name;
		$this->tables['relationships'] = book_database()->book_term_relationships->table_name;
		$this->tables['log']           = book_database()->reading_list->table_name;

		$allowed_orderby = array(
			'title'           => 'book.index_title, book.title',
			'author'          => 'author.name',
			'date'            => 'review.date_written',
			'pub_date'        => 'book.pub_date',
			'series_position' => 'book.series_position',
			'pages'           => 'book.pages'
		);

		$this->orderby = array_key_exists( $atts['orderby'], $allowed_orderby ) ? $allowed_orderby[ $atts['orderby'] ] : $allowed_orderby['title'];
		$this->order   = strtoupper( $atts['order'] ) == 'ASC' ? 'ASC' : 'DESC';
	}

	public function query( $filter = false ) {
	}

	/**
	 * Format Review
	 *
	 * @param object $review
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function format_review( $review ) {

		if ( ! is_object( $review ) ) {
			return '';
		}

		$review = wp_unslash( $review );

		$template = $this->template;
		$rating   = new BDB_Rating( $review->rating );

		$before_review = apply_filters( 'book-database/index/format_review/before', '<li>' );

		$find = array(
			'[link_start]',
			'[link_end]',
			'[title]',
			'[author]',
			'[series]',
			'[rating]',
		);

		// Get the URL
		$url = '';
		if ( ! empty( $review->post_id ) ) {
			$url = '<a href="' . esc_url( home_url( '/?p=' . absint( $review->post_id ) ) ) . '">';
		} elseif ( ! empty( $review->url ) ) {
			$url = '<a href="' . esc_url( $review->url ) . '" target="_blank">'; // new window
		}

		// Get the series
		$series = '';
		if ( $review->series_name && $review->series_position ) {
			$series = sprintf( '(%s #%s)', $review->series_name, $review->series_position );
		}

		$replace = array(
			$url,
			( ! empty( $url ) ) ? '</a>' : '',
			( ! empty( $review->index_title ) ) ? $review->index_title : $review->title,
			$review->author_name,
			$series,
			$rating->format( 'html_stars' )
		);

		$review_string = trim( str_replace( $find, $replace, $template ) );

		$after_review = apply_filters( 'book-database/index/format_review/before', '</li>' );

		return $before_review . apply_filters( 'book-database/index/format_review/review', $review_string, $review, $this ) . $after_review;

	}

	public function display() {
	}

}