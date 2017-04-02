<?php
/**
 * Analytics Ajax Callbacks
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
 * Batch 1
 *
 * Includes:
 *      + Number of reviews
 *      + Pages read
 *      + Average rating
 *      + Rating breakdown
 *
 * @since 1.0.0
 * @return void
 */
function bdb_analytics_batch_1() {

	$start = ( isset( $_POST['start'] ) && ! empty( $_POST['start'] ) ) ? wp_strip_all_tags( $_POST['start'] ) : date( 'Y-1-1', current_time( 'timestamp' ) );
	$end   = ( isset( $_POST['end'] ) && ! empty( $_POST['end'] ) ) ? wp_strip_all_tags( $_POST['end'] ) : 'now';
	$range = ( isset( $_POST['range'] ) && ! empty( $_POST['range'] ) ) ? wp_strip_all_tags( $_POST['range'] ) : 'this-year';

	$analytics = BDB_Analytics::instance();
	$analytics->set_dates( $start, $end );

	$date_hash = hash( 'md5', $analytics::$startstr . $analytics::$endstr );
	$results   = get_transient( 'bdb_analytics_1_' . $date_hash );
	$results   = false; // uncomment to debug

	if ( false == $results ) {

		$average_rating         = $analytics->get_average_rating();
		$rating_breakdown       = $analytics->get_rating_breakdown();
		$books_read             = $analytics->get_number_books_read();
		$available_ratings      = bdb_get_available_ratings();
		$rating_breakdown_final = array();

		// Reformat the rating breakdown.
		foreach ( $rating_breakdown as $rating => $number ) {
			if ( ! array_key_exists( $rating, $available_ratings ) ) {
				continue;
			}
			$rating_name              = $available_ratings[ $rating ];
			$rating_breakdown_final[] = array(
				'rating' => esc_html( $rating_name ),
				'count'  => absint( $number )
			);
		}

		$results = array(
			'number-books'            => $books_read['total'],
			'number-rereads'          => $books_read['rereads'],
			'number-new'              => $books_read['new'],
			'number-reviews'          => $analytics->get_number_reviews(),
			'number-different-series' => $analytics->get_number_different_series(),
			'number-standalones'      => $analytics->get_number_standalones(),
			'number-authors'          => $analytics->get_number_different_authors(),
			'pages'                   => $analytics->get_pages_read(),
			'avg-rating'              => ( ! empty( $average_rating ) ) ? sprintf( _n( '%s Star', '%s Stars', $average_rating, 'book-database' ), $average_rating ) : '&ndash;',
			'book-list'               => $analytics->get_book_list(),
			'read-not-reviewed'       => $analytics->get_read_not_reviewed(),
			'rating-breakdown'        => $rating_breakdown_final,
			'pages-breakdown'         => $analytics->get_pages_breakdown(),
			'reading-track'           => ''
		);

		if ( in_array( $range, array( 'this-month', 'this-year' ) ) ) {
			$range_label              = ( 'this-month' == $range ) ? __( 'this month', 'book-database' ) : __( 'this year', 'book-database' );
			$period_end               = ( 'this-month' == $range ) ? 'last day of this month' : date( 'Y-12-31 23:59:59' );
			$estimated_books          = bdb_get_books_on_track_to_read( $books_read['total'], $start, $period_end );
			$results['reading-track'] = sprintf( __( 'On track to read %d books %s.', 'book-database' ), $estimated_books, $range_label );
		}

		set_transient( 'bdb_analytics_1_' . $date_hash, $results, HOUR_IN_SECONDS );

	}

	wp_send_json_success( $results );

	exit;

}

add_action( 'wp_ajax_bdb_analytics_batch_1', 'bdb_analytics_batch_1' );

/**
 * Batch 1
 *
 * Includes:
 *      + Rating/count breakdowns for each term type.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_analytics_batch_2() {

	$start = ( isset( $_POST['start'] ) && ! empty( $_POST['start'] ) ) ? wp_strip_all_tags( $_POST['start'] ) : '-30 days';
	$end   = ( isset( $_POST['end'] ) && ! empty( $_POST['end'] ) ) ? wp_strip_all_tags( $_POST['end'] ) : 'now';

	$analytics = BDB_Analytics::instance();
	$analytics->set_dates( $start, $end );

	$date_hash = hash( 'md5', $analytics::$startstr . $analytics::$endstr );
	$results   = get_transient( 'bdb_analytics_2_' . $date_hash );
	$results   = false; // uncomment to debug

	if ( false == $results ) {

		$breakdown = $analytics->get_terms_breakdown();
		$results   = array();

		if ( is_array( $breakdown ) ) {
			foreach ( $breakdown as $term_info ) {
				$type             = $term_info->type;
				$existing_html    = array_key_exists( $type, $results ) ? $results[ $type ] : '';
				$average_rating   = ( null !== $term_info->avg_rating ) ? sprintf( _n( '%s Star', '%s Stars', $term_info->avg_rating ), floatval( $term_info->avg_rating ) ) : '&ndash;';
				$results[ $type ] = $existing_html . '<tr><td>' . esc_html( $term_info->name ) . '</td><td>' . sprintf( _n( '%s Book', '%s Books', $term_info->number_books ), $term_info->number_books ) . '</td><td>' . sprintf( _n( '%s Review', '%s Reviews', $term_info->number_reviews ), $term_info->number_reviews ) . '</td><td>' . $average_rating . '</td></tr>';
			}
		}

		set_transient( 'bdb_analytics_2_' . $date_hash, $results, HOUR_IN_SECONDS );

	}

	wp_send_json_success( $results );

	exit;

}

add_action( 'wp_ajax_bdb_analytics_batch_2', 'bdb_analytics_batch_2' );