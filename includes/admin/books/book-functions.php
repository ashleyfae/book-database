<?php
/**
 * Admin Book Functions
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
 * Register Default Book Views
 *
 * @param array $views
 *
 * @since 1.0.0
 * @return array
 */
function bdb_register_default_book_views( $views ) {

	$default_views = array(
		'add'  => 'bdb_books_edit_view',
		'edit' => 'bdb_books_edit_view'
	);

	return array_merge( $views, $default_views );

}

add_filter( 'book-database/books/views', 'bdb_register_default_book_views' );