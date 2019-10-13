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
 * Validate integers, but allow null.
 *
 * @param $value
 *
 * @return int|null
 */
function absint_allow_null( $value ) {
	return '' === $value ? null : absint( $value );
}