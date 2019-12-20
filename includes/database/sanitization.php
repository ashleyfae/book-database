<?php
/**
 * Custom Column Sanitization Callbacks
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\BerlinDB\Sanitization;

/**
 * Sanitizes integers, but allow null.
 *
 * @param $value
 *
 * @return int|null
 */
function absint_allow_null( $value ) {
	return '' === $value ? null : absint( $value );
}

/**
 * If null or empty string, return null
 * If integer, run through `absint()`
 * If floatval, run through `floatval()`
 *
 * @param $value
 *
 * @since 1.0.3
 * @return float|int|null
 */
function floatval_int_allow_null( $value ) {

	if ( is_null( $value ) || '' === $value ) {
		return null;
	} elseif ( is_int( $value ) ) {
		return absint( $value );
	} else {
		return floatval( $value );
	}
}

/**
 * Sanitize a date value
 *
 * @param string|null $value
 *
 * @return string|null
 */
function validate_date( $value ) {

	if ( empty( $value ) ) {
		return null;
	}

	return date( 'Y-m-d', strtotime( $value ) );

}