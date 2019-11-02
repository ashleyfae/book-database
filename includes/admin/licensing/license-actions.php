<?php
/**
 * License & Automatic Update Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Initialize the updater
 */
function initialize_updater() {

	$license_key = trim( get_option( 'bdb_license_key' ) );

	if ( empty( $license_key ) ) {
		return;
	}

	new EDD_SL_Plugin_Updater( NOSE_GRAZE_STORE_URL, BDB_FILE, array(
		'version' => BDB_VERSION,
		'license' => $license_key,
		'item_id' => 48100,
		'author'  => 'Nose Graze',
		'beta'    => false // @todo
	) );

}

add_action( 'admin_init', __NAMESPACE__ . '\initialize_updater', 0 );

/**
 * Activate a license key
 */
function activate_license_key() {

	check_ajax_referer( 'bdb_activate_license_key', 'nonce' );

	try {

		if ( ! user_can_manage_book_settings() ) {
			throw new Exception( 'no-permission', __( 'You do not have permission to perform this action.', 'book-database' ) );
		}

		if ( empty( $_POST['license_key'] ) ) {
			throw new Exception( 'missing-parameter', __( 'Please enter a license key.', 'book-database' ) );
		}

		$license = new License_Key( $_POST['license_key'] );
		$license->activate();

		if ( 'lifetime' === $license->get_expiration_date() ) {
			$message = sprintf( __( 'Successfully activated. Your license key never expires.', 'book-database' ) );
		} elseif ( empty( $license->get_expiration_date() ) ) {
			$message = sprintf( __( 'Successfully activated.', 'book-database' ) );
		} else {
			$message = sprintf( __( 'Successfully activated. Your license key expires on %s.', 'book-database' ), $license->get_expiration_date( true ) );
		}

		wp_send_json_success( $message );

	} catch ( Exception $e ) {
		wp_send_json_error( $e->getMessage() );
	}

	exit;

}

add_action( 'wp_ajax_bdb_activate_license_key', __NAMESPACE__ . '\activate_license_key' );

/**
 * Deactivate a license key
 */
function deactivate_license_key() {

	check_ajax_referer( 'bdb_deactivate_license_key', 'nonce' );

	try {

		if ( ! user_can_manage_book_settings() ) {
			throw new Exception( 'no-permission', __( 'You do not have permission to perform this action.', 'book-database' ) );
		}

		if ( empty( $_POST['license_key'] ) ) {
			throw new Exception( 'missing-parameter', __( 'Please enter a license key.', 'book-database' ) );
		}

		$license = new License_Key( $_POST['license_key'] );
		$license->deactivate();

		wp_send_json_success( __( 'Successfully deactivated.', 'book-database' ) );

	} catch ( Exception $e ) {
		wp_send_json_error( $e->getMessage() );
	}

	exit;

}

add_action( 'wp_ajax_bdb_deactivate_license_key', __NAMESPACE__ . '\deactivate_license_key' );

/**
 * Refresh a license key
 */
function refresh_license_key() {

	check_ajax_referer( 'bdb_refresh_license_key', 'nonce' );

	try {

		if ( ! user_can_manage_book_settings() ) {
			throw new Exception( 'no-permission', __( 'You do not have permission to perform this action.', 'book-database' ) );
		}

		if ( empty( $_POST['license_key'] ) ) {
			throw new Exception( 'missing-parameter', __( 'Please enter a license key.', 'book-database' ) );
		}

		delete_option( 'bdb_license_key_data' );

		$license = new License_Key( $_POST['license_key'] );

		wp_send_json_success( $license->get_status_message() );

	} catch ( Exception $e ) {
		wp_send_json_error( $e->getMessage() );
	}

	exit;

}

add_action( 'wp_ajax_bdb_refresh_license_key', __NAMESPACE__ . '\refresh_license_key' );