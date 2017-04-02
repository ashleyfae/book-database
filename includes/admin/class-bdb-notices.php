<?php

/**
 * Admin Notices
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
 * Class BDB_Notices
 *
 * @since 1.0.0
 */
class BDB_Notices {

	/**
	 * BDB_Notices constructor.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
	}

	/**
	 * Show Notices
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function show_notices() {

		$notices = array(
			'updated' => array(),
			'error'   => array(),
		);

		if ( isset( $_GET['bdb-message'] ) ) {

			// Books
			switch ( $_GET['bdb-message'] ) {
				case 'book-updated' :
					$notices['updated']['bookdb-book-updated'] = __( 'Book successfully updated.', 'book-database' );
					break;
				case 'book-deleted' :
					$notices['updated']['bookdb-book-deleted'] = __( 'The book has been deleted.', 'book-database' );
					break;
				case 'book-delete-failed' :
					$notices['error']['bookdb-book-delete-failed'] = __( 'There was an error deleting the book.', 'book-database' );
					break;
			}

			// Reviews
			switch ( $_GET['bdb-message'] ) {
				case 'review-updated' :
					$notices['updated']['bookdb-review-updated'] = __( 'Review successfully updated.', 'book-database' );
					break;
				case 'review-deleted' :
					$notices['updated']['bookdb-review-deleted'] = __( 'The review has been deleted.', 'book-database' );
					break;
				case 'review-delete-failed' :
					$notices['error']['bookdb-review-delete-failed'] = __( 'There was an error deleting the review.', 'book-database' );
					break;
			}

		}

		if ( count( $notices['updated'] ) > 0 ) {
			foreach ( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'bdb-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach ( $notices['error'] as $notice => $message ) {
				add_settings_error( 'bdb-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'bdb-notices' );

	}

}

new BDB_Notices;