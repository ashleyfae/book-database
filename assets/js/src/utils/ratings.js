/* global $, bdbVars */

/**
 * Convert a numberical star rating into HTML stars
 *
 * @param rating
 * @returns {string}
 */
export function getStars( rating ) {

	let html = '';
	let fullStars = Math.floor( rating );
	let halfStars = Math.ceil( rating - fullStars );
	let fullStarString = '&starf;';
	let halfStarString = '&half;';

	html += fullStarString.repeat( fullStars );
	html += halfStarString.repeat( halfStars );

	return html;

}