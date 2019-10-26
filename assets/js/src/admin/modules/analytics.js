/* global $, bdbVars, wp */

import { dateLocalToUTC } from "./dates";
import { apiRequest, spinButton, unspinButton } from 'utils';
import { BDB_Datepicker } from './datepicker';

/**
 * Analytics
 */
var BDB_Analytics = {

	/**
	 * Selected date range value
	 */
	range: 'this-year',

	batches: [],

	numbers: [
		'number-books-finished', 'number-dnf', 'number-new-books', 'number-rereads', 'number-pages-read', 'reading-track',
		'number-reviews', 'avg-rating', 'number-different-series', 'number-standalones', 'number-authors'
	],

	tables: [
		'rating-breakdown', 'pages-breakdown'
	],

	/**
	 * Initialize
	 */
	init: function() {
		if ( ! document.getElementById( 'bdb-book-analytics-wrap') ) {
			return;
		}

		$( '.bdb-taxonomy-breakdown' ).each( function() {
			let id = $( this ).attr( 'id' ).replace( 'bdb-', '' );
			BDB_Analytics.tables.push( id );
		} );

		console.log(BDB_Analytics.tables);

		BDB_Analytics.batches = [
			BDB_Analytics.numbers,
			BDB_Analytics.tables
		];

		$( '#bdb-range' ).on( 'change', this.setRanges );
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

		BDB_Analytics.range = $( this ).val();

		if ( 'custom' === BDB_Analytics.range ) {

			$( '#bdb-start' ).val( '' ).attr( 'type', 'text' ).addClass( 'bdb-datepicker' );
			$( '#bdb-end' ).val( '' ).attr( 'type', 'text' ).addClass( 'bdb-datepicker' );

			BDB_Datepicker.setDatepickers();

		} else {

			let selected = $( this ).find( 'option:selected' );
			$( '#bdb-start' ).val( selected.data( 'start' ) ).attr( 'type', 'hidden' ).removeClass( 'bdb-datepicker' );
			$( '#bdb-end' ).val( selected.data( 'end' ) ).attr( 'type', 'hidden' ).removeClass( 'bdb-datepicker' );

		}

	},

	/**
	 * Get the HTML string to insert into the DOM to indicate the stat is loading.
	 *
	 * @returns {string}
	 */
	getLoadingString: function () {
		return '<div id="bdb-circleG"><div id="bdb-circleG_1" class="bdb-circleG"></div><div id="bdb-circleG_2" class="bdb-circleG"></div><div id="bdb-circleG_3" class="bdb-circleG"></div></div>';
	},

	/**
	 * Get the stats for the provided date range
	 */
	getStats: function () {

		// Empty the results and set up spinners.
		$( '.bdb-result' ).empty();
		$( '.bdb-loading' ).html( BDB_Analytics.getLoadingString() ).show();

		for ( let i = 0; i < BDB_Analytics.batches.length; i++ ) {

			let args = {
				start_date: dateLocalToUTC( $( '#bdb-start' ).val() ),
				end_date: dateLocalToUTC( $( '#bdb-end' ).val() ),
				stats: BDB_Analytics.batches[ i ]
			};

			apiRequest( 'v1/analytics', args, 'GET' ).then( function( apiResponse ) {

				$.each( apiResponse, function( statKey, statValue ) {
					let wrap = $( '#bdb-' + statKey );

					// Set up format for "on track to read".
					if ( 'reading-track' === statKey ) {
						if ( 'this-month' === BDB_Analytics.range ) {
							statValue = bdbVars.on_track_month.replace( '%d', statValue );
						} else if ( 'this-year' === BDB_Analytics.range ) {
							statValue = bdbVars.on_track_year.replace( '%d', statValue );
						} else {
							statValue = '';
						}
					}

					// Check for an Underscore.js template.
					if ( Array === statValue.constructor && document.getElementById( 'tmpl-bdb-analytics-' + statKey + '-table-row' ) ) {
						let template = wp.template( 'bdb-analytics-' + statKey + '-table-row' );
						let html = '';

						$.each( statValue, function( statItemKey, statItem ) {
							html += template( statItem );
						} );

						statValue = html;
					}

					wrap.find( '.bdb-result' ).empty().append( statValue );
					wrap.find( '.bdb-loading' ).empty().hide();
				} )

			} ).catch( function( error ) {

			} );

		}

	}

};

export { BDB_Analytics }