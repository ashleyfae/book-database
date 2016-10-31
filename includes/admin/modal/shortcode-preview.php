<?php
/**
 * Shortcode Preview
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
 * Render Shortcode Preview
 *
 * @todo  Turn this into real book preview.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_tinymce_shortcode_preview() {
	check_ajax_referer( 'book-database', 'nonce' );

	$book_id = isset( $_POST['book_id'] ) ? absint( $_POST['book_id'] ) : 0;
	$book    = new BDB_Book( $book_id );

	$template = '[title] [author]';

	$title  = $book->get_title() ? '<strong>' . $book->get_title() . '</strong>' : '';
	$author = $book->get_author_names() ? sprintf( __( ' by %s', 'book-database' ), $book->get_author_names() ) : '';

	$find = array(
		'[title]',
		'[author]'
	);

	$replace = array( $title, $author );

	wp_send_json_success( str_replace( $find, $replace, $template ) );
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