/* global $, bdbVars */

/**
 * Make a request to the REST API
 *
 * @param {string} endpoint
 * @param {object} data
 * @param {string} method
 * @returns {Promise}
 */
export function apiRequest( endpoint, data = {}, method = 'POST' ) {
	const options = {
		method: method,
		url: bdbVars.api_base + 'book-database/' + endpoint,
		beforeSend: function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', bdbVars.api_nonce );
		},
		xhrFields: {
			withCredentials: true,
		},
		data: data
	};

	console.log( 'API endpoint', endpoint );

	return new Promise( function ( resolve, reject ) {
		$.ajax( options ).success( function ( response ) {
			console.log( 'Success response', response );
			resolve( response );
		} ).error( function ( qpXHR, textStatus, errorThrown ) {
			let error = bdbVars.generic_erroc;

			if ( 'undefined' !== typeof qpXHR.responseJSON ) {
				error = qpXHR.responseJSON;

				if ( 'undefined' !== typeof error.message ) {
					error = error.message;
				}
			} else if ( 'undefined' !== typeof qpXHR.message ) {
				error = qpXHR.message;
			}

			reject( error );
		} );
	} )
}