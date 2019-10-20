/* global $, bdbVars, wp */

import { dateLocalToUTC } from "./dates";
import { apiRequest, spinButton, unspinButton } from 'utils';
import { BDB_Datepicker } from './datepicker';

/**
 * Analytics
 */
var BDB_Analytics = {

	batches: [
		[ 'number-books-finished', 'number-dnf', 'number-new-books', 'number-rereads', 'number-pages-read', 'reading-track' ]
	],

	/**
	 * Initialize
	 */
	init: function() {
		if ( ! document.getElementById( 'bdb-book-analytics-wrap') ) {
			return;
		}

		$( '#bdb-date-range' ).on( 'change', this.setRanges );
		$( '#bdb-date-range button' ).on( 'click', function ( e ) {
			e.preventDefault();

			BDB_Analytics.getStats();
		} ).trigger( 'click' );
	},

	/**
	 * Set the date ranges
	 *
	 * @param e
	 */
	setRanges: function ( e ) {

		if ( 'custom' === $( this ).val() ) {

			$( '#bdb-start' ).val( '' ).attr( 'type', 'text' ).addClass( 'bdb-datepicker' );
			$( '#bdb-end' ).val( '' ).attr( 'type', 'text' ).addClass( 'bdb-datepicker' );

			BDB_Datepicker.setDatepickers();

		} else {

			let selected = $( this ).find( 'option:selected' );
			$( '#bdb-start' ).val( selected.data( 'start' ) ).attr( 'type', 'hidden' ).removeClass( 'bdb-datepicker' );
			$( '#bdb-end' ).val( selected.data( 'end' ) ).attr( 'type', 'hidden' ).removeClass( 'bdb-datepicker' );

		}

	},

	getStats: function () {

		let loading = '<div id="circleG"><div id="circleG_1" class="circleG"></div><div id="circleG_2" class="circleG"></div><div id="circleG_3" class="circleG"></div></div>';

		// Empty the results and set up spinners.
		$( '.bdb-result' ).empty();
		$( '.bdb-loading' ).html( loading ).show();

		for ( let i = 0; i < BDB_Analytics.batches.length; i++ ) {

			let args = {
				start_date: dateLocalToUTC( $( '#bdb-start' ).val() ),
				end_date: dateLocalToUTC( $( '#bdb-end' ).val() ),
				stats: BDB_Analytics.batches[ i ]
			};

			apiRequest( 'v1/analytics', args, 'GET' ).then( function( apiResponse ) {



			} ).catch( function( error ) {

			} );

		}

	}

};

export { BDB_Analytics }