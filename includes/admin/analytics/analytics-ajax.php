<?php
/**
 * Analytics Ajax Callbacks
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
 * Batch 1
 *
 * Includes:
 *      + Number of reviews
 *      + Pages read
 *      + Average rating
 *
 * @since 1.0.0
 * @return void
 */
function bdb_analytics_batch_1() {

	$start = ( isset( $_POST['start'] ) && ! empty( $_POST['start'] ) ) ? wp_strip_all_tags( $_POST['start'] ) : '-30 days';
	$end   = ( isset( $_POST['end'] ) && ! empty( $_GET['end'] ) ) ? wp_strip_all_tags( $_POST['end'] ) : 'now';

	$analytics = BDB_Analytics::instance();
	$analytics->set_dates( $start, $end );

	$date_hash = hash( 'md5', $analytics::$startstr . $analytics::$endstr );
	$results   = get_transient( 'bdb_analytics_1_' . $date_hash );
	$results   = false;

	if ( false == $results ) {

		$rating = $analytics->get_average_rating();

		$results = array(
			'number-reviews' => $analytics->get_number_reviews(),
			'pages'          => $analytics->get_pages_read(),
			'avg-rating'     => sprintf( _n( '%s Star', '%s Stars', $rating, 'book-database' ), $rating )
		);

		set_transient( 'bdb_analytics_1_' . $date_hash, $results, HOUR_IN_SECONDS );

	}

	wp_send_json_success( $results );

	exit;

}

add_action( 'wp_ajax_bdb_analytics_batch_1', 'bdb_analytics_batch_1' );