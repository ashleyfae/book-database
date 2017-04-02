<?php
/**
 * Rating Functions
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
 * Get Available Ratings
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_available_ratings() {
	$ratings = array(
		'5'   => esc_html__( '5 Stars', 'book-database' ),
		'4.5' => esc_html__( '4.5 Stars', 'book-database' ),
		'4'   => esc_html__( '4 Stars', 'book-database' ),
		'3.5' => esc_html__( '3.5 Stars', 'book-database' ),
		'3'   => esc_html__( '3 Stars', 'book-database' ),
		'2.5' => esc_html__( '2.5 Stars', 'book-database' ),
		'2'   => esc_html__( '2 Stars', 'book-database' ),
		'1.5' => esc_html__( '1.5 Stars', 'book-database' ),
		'1'   => esc_html__( '1 Star', 'book-database' ),
		'0.5' => esc_html__( '0.5 Stars', 'book-database' ),
		'0'   => esc_html__( '0 Stars', 'book-database' ),
		'dnf' => esc_html__( 'Did Not Finish', 'book-database' )
	);

	return apply_filters( 'book-database/ratings/available-ratings', $ratings );
}

/**
 * Get Maximum Rating
 *
 * @since 1.0.0
 * @return int
 */
function bdb_get_max_rating() {
	return apply_filters( 'book-database/ratings/max-rating', 5 );
}