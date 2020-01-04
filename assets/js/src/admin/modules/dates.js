/* global $, bdbVars, moment */

const formatMySQL = 'YYYY-MM-DD HH:mm:ss';
const formatDisplay = 'MMMM D, YYYY';

/**
 * Converts a UTC date string to local time in YYYY-mm-dd format.
 *
 * @param {string} utcDate
 * @param {string} format
 * @returns {string}
 */
export function dateUTCtoLocal( utcDate, format = 'mysql' ) {

	if ( '' === utcDate || ! utcDate) {
		return '';
	}

	if ( 'display' === format ) {
		format = formatDisplay;
	} else {
		format = formatMySQL;
	}

	utcDate = moment.utc( utcDate );

	//console.log( 'UTC Date', utcDate );

	let localDate = utcDate.local().format( format );

	//console.log( 'Local Date', localDate );

	return localDate;

}

/**
 * Converts a local date string to UTC in YYYY-mm-dd format.
 *
 * @param {string} localDate
 * @returns {string}
 */
export function dateLocalToUTC( localDate ) {

	if ( '' === localDate || ! localDate ) {
		return '';
	}

	localDate = moment( localDate );

	//console.log( 'Local Date', localDate );

	let utcDate = localDate.utc().format( formatMySQL );

	//console.log( 'UTC Date', utcDate );

	return utcDate;

}