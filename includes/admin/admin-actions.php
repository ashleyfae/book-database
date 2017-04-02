<?php
/**
 * Admin Actions
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
 * Processes all BDB actions sent via POST and GET by looking for the 'bdb-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function bdb_process_actions() {
	if ( isset( $_POST['bdb-action'] ) ) {
		do_action( 'book-database/' . $_POST['bdb-action'], $_POST );
	}

	if ( isset( $_GET['bdb-action'] ) ) {
		do_action( 'book-database/' . $_GET['bdb-action'], $_GET );
	}
}

add_action( 'admin_init', 'bdb_process_actions' );