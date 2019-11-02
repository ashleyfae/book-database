/* global $, bdbVars, wp, ajaxurl */

import { ajaxRequest, spinButton, unspinButton } from 'utils';

/**
 * License Key
 */
var BDB_License = {

	responseWrap: false,

	/**
	 * Initialize
	 */
	init: function() {

		this.responseWrap = $( '#bdb-license-key-response' );

		$( '#bdb-activate-license-key' ).on( 'click', this.activate );
		$( '#bdb-deactivate-license-key' ).on( 'click', this.deactivate );
		$( '#bdb-refresh-license-key' ).on( 'click', this.refresh );

	},

	/**
	 * Activate a license key
	 *
	 * @param e
	 */
	activate: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );
		BDB_License.responseWrap.empty().removeClass( 'bdb-notice bdb-notice-error' );

		let args = {
			action: 'bdb_activate_license_key',
			license_key: $( '#bdb-license-key' ).val(),
			nonce: button.data( 'nonce' )
		};

		ajaxRequest( args ).then( function( apiResponse ) {

			BDB_License.responseWrap.empty().addClass( 'bdb-notice bdb-notice-success' ).append( apiResponse );
			$( '#bdb-activate-license-key' ).remove();

		} ).catch( function( errorMessage ) {
			BDB_License.responseWrap.empty().addClass( 'bdb-notice bdb-notice-error' ).append( errorMessage );
			unspinButton( button );
		} );

	},

	/**
	 * Deactivate a license key
	 *
	 * @param e
	 */
	deactivate: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );
		BDB_License.responseWrap.empty().removeClass( 'bdb-notice bdb-notice-error' );

		let args = {
			action: 'bdb_deactivate_license_key',
			license_key: $( '#bdb-license-key' ).val(),
			nonce: button.data( 'nonce' )
		};

		ajaxRequest( args ).then( function( apiResponse ) {

			BDB_License.responseWrap.empty().addClass( 'bdb-notice bdb-notice-success' ).append( apiResponse );
			$( '#bdb-deactivate-license-key' ).remove();

		} ).catch( function( errorMessage ) {
			BDB_License.responseWrap.empty().addClass( 'bdb-notice bdb-notice-error' ).append( errorMessage );
			unspinButton( button );
		} );

	},

	/**
	 * Refresh the license key status
	 *
	 * @param e
	 */
	refresh: function ( e ) {

		e.preventDefault();

		let button = $( this ),
			wrap = button.parent().find( '.description' );

		spinButton( button );
		BDB_License.responseWrap.empty().removeClass( 'bdb-notice bdb-notice-error' );

		let args = {
			action: 'bdb_refresh_license_key',
			license_key: $( '#bdb-license-key' ).val(),
			nonce: button.data( 'nonce' )
		};

		ajaxRequest( args ).then( function( apiResponse ) {

			wrap.empty().append( apiResponse );

		} ).catch( function( errorMessage ) {
			BDB_License.responseWrap.empty().addClass( 'bdb-notice bdb-notice-error' ).append( errorMessage );
		} ).finally( function() {
			unspinButton( button );
		} );

	}

};

export { BDB_License }