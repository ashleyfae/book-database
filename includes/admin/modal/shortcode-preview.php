<?php
/**
 * Shortcode Preview
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
 * Render Shortcode Preview
 *
 * @since 1.0
 * @return void
 */
function bdb_tinymce_shortcode_preview() {
	check_ajax_referer( 'book-database', 'nonce' );

	define( 'BDB_TINYMCE', true );

	$book_id      = isset( $_POST['book_id'] ) ? absint( $_POST['book_id'] ) : 0;
	$book         = new BDB_Book( $book_id );
	$button_style = 'background: #f7f7f7; border: 1px solid #ccc; border-radius: 3px; box-shadow: 0 1px 0 #ccc; color: #555; cursor: pointer; display: inline-block; font-size: 13px; line-height: 27px; height: 28px; margin: 0 5px 4px 0; padding: 0 10px 1px;';

	if ( ! empty( $_POST['rating'] ) ) {
		$book->set_rating( sanitize_text_field( $_POST['rating'] ) );
	}

	$buttons = '<button type="button" style="' . esc_attr( $button_style ) . '">' . esc_html__( 'Edit Book', 'book-database' ) . '</button>';
	$buttons .= '<button type="button" data-bookdb-book-remove="' . esc_attr( $book_id ) . '" style="' . esc_attr( $button_style ) . '">' . esc_html__( 'Remove Book', 'book-database' ) . '</button>';
	//$preview = $book->get_formatted_info() . $buttons; // This was causing formatting issues. Grr.

	$title  = sprintf( __( '%s by %s', 'book-database' ), $book->get_title(), $book->get_author_names() );
	$rating = '';

	if ( ! empty( $_POST['rating'] ) ) {
		$rating_obj = new BDB_Rating( sanitize_text_field( $_POST['rating'] ) );
		$rating     = '<p style="margin-top: 0;">' . $rating_obj->format_html_stars() . '</p>';
	}

	$preview = $book->get_cover( 'thumbnail', array( 'class' => 'alignleft' ) ) . '<h2 style="clear: none; margin: .5em 0 5px;">' . $title . '</h2>' . $rating . $buttons;

	wp_send_json_success( $preview );
}

add_action( 'wp_ajax_bdb_shortcode_preview', 'bdb_tinymce_shortcode_preview' );

/**
 * Shortcode Plugin JavaScript
 *
 * @param array $plugin_array
 *
 * @since 1.0
 * @return array
 */
function bdb_tinymce_shortcode_plugin( $plugin_array ) {
	$suffix                       = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$plugin_array['bookdatabase'] = BDB_URL . 'assets/js/admin/shortcode-tinymce' . $suffix . '.js';

	return $plugin_array;
}

add_filter( 'mce_external_plugins', 'bdb_tinymce_shortcode_plugin' );