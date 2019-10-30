/* global $, bdbVars */

import {utc} from "moment";

let moment = require( 'moment' );

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

	//console.log( 'UTC Date', utcDate );

	let t = utcDate.split( /[- :]/ );

	let localDate = new Date( Date.UTC( t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0 ) );
	localDate = moment( localDate ).format( format );

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

	//console.log( 'Local Date', localDate );

	let t = localDate.split( /[- :]/ );

	let newDate = new Date( t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0 ).toISOString();
	let utcDate = moment.utc( newDate ).format( formatMySQL );

	//console.log( 'UTC Date', utcDate );

	return utcDate;

}