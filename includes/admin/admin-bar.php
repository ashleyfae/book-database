<?php
/**
 * Admin Bar
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 * @since     1.1.1
 */

namespace Book_Database\Admin;

use function Book_Database\get_books_admin_page_url;
use function Book_Database\user_can_edit_books;

/**
 * Registers a new node on the admin bar
 *
 * @param \WP_Admin_Bar $wp_admin_bar
 *
 * @since 1.1.1
 */
function register_menu( $wp_admin_bar ) {
	if ( ! $wp_admin_bar instanceof \WP_Admin_Bar ) {
		return;
	}

	if ( ! user_can_edit_books() ) {
		return;
	}

	$wp_admin_bar->add_node( array(
		'id'     => 'bdb-add-book',
		'title'  => __( 'Book', 'book-database' ),
		'parent' => 'new-content',
		'href'   => get_books_admin_page_url( array( 'view' => 'add' ) ),
	) );
}

add_action( 'admin_bar_menu', __NAMESPACE__ . '\register_menu', 300 );