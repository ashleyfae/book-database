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
 * @since 1.0.0
 * @return void
 */
function bdb_tinymce_shortcode_preview() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_id = isset( $_POST['book_id'] ) ? absint( $_POST['book_id'] ) : 0;
	$book    = new BDB_Book( $book_id );

	$remove  = '<span data-bookdb-book-remove="' . esc_attr( $book_id ) . '" style="color: red; cursor: pointer; float: right;">' . esc_html__( 'Remove', 'book-database' ) . '</span>';
	$preview = $remove . $book->get_formatted_info();

	wp_send_json_success( $preview );
}

add_action( 'wp_ajax_bdb_shortcode_preview', 'bdb_tinymce_shortcode_preview' );

/**
 * Shortcode Plugin JavaScript
 *
 * @param array $plugin_array
 *
 * @since 1.0.0
 * @return array
 */
function bdb_tinymce_shortcode_plugin( $plugin_array ) {
	$suffix                       = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$plugin_array['bookdatabase'] = BDB_URL . 'assets/js/admin/shortcode-tinymce' . $suffix . '.js';

	return $plugin_array;
}

add_filter( 'mce_external_plugins', 'bdb_tinymce_shortcode_plugin' );