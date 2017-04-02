<?php
/**
 * Error Tracking
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
 * Error Message Logging
 *
 * Creates a new instance of WP_Error for our error messages.
 *
 * @return WP_Error
 */
function bdb_errors() {
	static $wp_error;

	return isset( $wp_error ) ? $wp_error : ( $wp_error = new WP_Error( null, null, null ) );
}

/**
 * Print Errors
 *
 * Prints all stored errors.
 *
 * @uses  bdb_get_errors()
 * @uses  bdb_clear_errors()
 *
 * @since 1.0.0
 * @return void
 */
function bdb_print_errors() {

	$errors = bdb_get_errors();
	if ( $errors ) {
		$classes = apply_filters( 'book-database/error-class', array(
			'bdb_errors',
			'ubb-alert',
			'ubb-alert-error'
		) );
		echo '<div class="' . implode( ' ', $classes ) . '">';
		// Loop error codes and display errors
		foreach ( $errors as $error_id => $error ) {
			echo '<p class="ubb-error" id="ubb-error-' . $error_id . '"><strong>' . __( 'Error', 'book-database' ) . '</strong>: ' . $error . '</p>';
		}
		echo '</div>';
		bdb_clear_errors();
	}

}

/**
 * Get Errors
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_errors() {
	return bdb_errors()->errors;
}

/**
 * Set Error
 *
 * @param string|int $error_id      ID of the error.
 * @param string     $error_message Error message.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_set_error( $error_id, $error_message ) {
	bdb_errors()->add( $error_id, $error_message );
}

/**
 * Remove Error
 *
 * @param string|int $error_id ID of the error to remove.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_unset_error( $error_id ) {
	bdb_errors()->remove( $error_id );
}

/**
 * Clear All Errors
 *
 * @uses  bdb_unset_error()
 *
 * @since 1.0.0
 * @return void
 */
function bdb_clear_errors() {
	$codes = bdb_errors()->get_error_codes();

	if ( $codes && is_array( $codes ) ) {
		foreach ( $codes as $code ) {
			bdb_unset_error( $code );
		}
	}
}