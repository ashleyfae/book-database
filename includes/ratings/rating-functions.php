<?php
/**
 * Rating Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get the available ratings
 *
 * @return array
 */
function get_available_ratings() {

	$ratings = array(
		'5'   => __( '5 Stars', 'book-database' ),
		'4.5' => __( '4.5 Stars', 'book-database' ),
		'4'   => __( '4 Stars', 'book-database' ),
		'3.5' => __( '3.5 Stars', 'book-database' ),
		'3'   => __( '3 Stars', 'book-database' ),
		'2.5' => __( '2.5 Stars', 'book-database' ),
		'2'   => __( '2 Stars', 'book-database' ),
		'1.5' => __( '1.5 Stars', 'book-database' ),
		'1'   => __( '1 Star', 'book-database' ),
		'0.5' => __( '0.5 Stars', 'book-database' ),
		'0'   => __( '0 Stars', 'book-database' ),
	);

	/**
	 * Filters the available ratings.
	 *
	 * @param array $ratings
	 */
	return apply_filters( 'book-database/ratings/available-ratings', $ratings );

}