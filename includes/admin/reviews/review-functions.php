<?php
/**
 * Admin Review Functions
 *
 * @package   review-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Default Book Views
 *
 * @param array $views
 *
 * @since 1.0.0
 * @return array
 */
function bdb_register_default_review_views( $views ) {

	$default_views = array(
		'add'  => 'bdb_reviews_edit_view',
		'edit' => 'bdb_reviews_edit_view'
	);

	return array_merge( $views, $default_views );

}

add_filter( 'book-database/reviews/views', 'bdb_register_default_review_views' );

/**
 * Ajax CB: Update or Create Review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_save_review() {
	check_ajax_referer( 'book-database', 'nonce' );

	$review_data = isset( $_POST['review'] ) ? $_POST['review'] : array();

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( __( 'Error: You do not have permission to add reviews.', 'book-database' ) );
	}

	$new_review_id = bdb_insert_review( $review_data );
	$review        = new BDB_Review( $new_review_id );
	$book          = new BDB_Book( $review->book_id );
	$rating        = new BDB_Rating( $review->get_rating() );

	$data = array(
		'ID'        => $new_review_id . ' <a href="' . esc_url( bdb_get_admin_page_edit_review( $new_review_id ) ) . '" target="_blank">' . __( '(Edit)', 'book-database' ) . '</a>',
		'book'      => esc_html( sprintf( _x( '%s by %s', 'book title by author name', 'book-database' ), $book->get_title(), $book->get_author_names() ) ),
		'rating'    => $review->rating ? $rating->format( 'text' ) : '&ndash;',
		'shortcode' => '[book id="' . esc_attr( $book->ID ) . '" rating="' . esc_attr( $review->get_rating() ) . '"]',
		'remove'    => '<button class="button secondary bookdb-remove-book-review">' . __( 'Remove', 'book-database' ) . '</button>'
	);

	wp_send_json_success( $data );
}

add_action( 'wp_ajax_bdb_save_review', 'bdb_ajax_save_review' );

/**
 * Ajax CB: Remove Review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_remove_review() {
	check_ajax_referer( 'book-database', 'nonce' );

	$review_id = $_POST['review_id'];

	if ( ! $review_id ) {
		wp_send_json_error( __( 'Invalid review ID.', 'book-database' ) );
	}

	book_database()->reviews->delete( absint( $review_id ) );

	wp_send_json_success();

	exit;
}

add_action( 'wp_ajax_bdb_remove_review', 'bdb_ajax_remove_review' );

/**
 * Ajax CB: Get Book's Reading Logs
 *
 * @since 1.2.2
 * @return void
 */
function bdb_ajax_get_book_reading_logs() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_id = $_POST['book_id'];

	if ( empty( $book_id ) ) {
		wp_send_json_error( __( 'Invalid book ID.', 'book-database' ) );
	}

	$reading_logs = bdb_get_book_reading_list( absint( $book_id ) );
	$entries      = array();

	if ( is_array( $reading_logs ) ) {
		foreach ( $reading_logs as $log ) {
			$rating              = new BDB_Rating( $log->rating );
			$entries[ $log->ID ] = sprintf( '%s - %s [%s]', bdb_format_mysql_date( $log->date_started, 'j M Y' ), bdb_format_mysql_date( $log->date_finished, 'j M Y' ), $rating->format_text() );
		}
	}

	wp_send_json_success( $entries );
}

add_action( 'wp_ajax_bdb_get_book_reading_logs', 'bdb_ajax_get_book_reading_logs' );