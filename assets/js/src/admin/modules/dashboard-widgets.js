/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton, getStars } from 'utils';

/**
 * Admin Dashboard Widgets
 */
var BDB_Dashboard_Widgets = {

	/**
	 * Initialize
	 */
	init: function() {

		$( '.bdb-currently-reading-widget-update-progress' ).on( 'click', this.updatePercentage );
		$( '.bdb-currently-reading-widget-finish-book' ).on( 'click', this.finishBook );
		$( '.bdb-currently-reading-widget-dnf-book' ).on( 'click', this.dnfBook );
		$( '.bdb-currently-reading-widget-set-rating' ).on( 'click', this.setRating );

	},

	/**
	 * Update the percentage
	 *
	 * @param e
	 */
	updatePercentage: function ( e ) {

		e.preventDefault();

		let button = $( this ),
			wrap = button.closest( 'li' ),
			logID = wrap.data( 'log-id' ),
			percentage = prompt( bdbVars.prompt_percentage ),
			progressWrap = button.parent().find( '.bdb-currently-reading-progress-bar' ),
			progressNumber = button.parent().find( '.bdb-currently-reading-progress-number' );

		if ( null === percentage || '0' === percentage ) {
			return;
		}

		spinButton( button );

		let args = {
			percentage_complete: percentage / 100
		};

		apiRequest( 'v1/reading-log/update/' + logID, args, 'POST' ).then( function( apiResponse ) {
			progressWrap.css( 'width', percentage + '%' );
			progressNumber.text( percentage + '%' );
		} ).catch( function( errorMessage ) {
			console.log( errorMessage );
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Finish a book
	 *
	 * This sets the percentage to 100% and the finished date to today.
	 *
	 * @param e
	 */
	finishBook: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_finish_book ) ) {
			return false;
		}

		let button = $( this );

		spinButton( button );

		let wrap = button.closest( 'li' );

		let args = {
			percentage_complete: 1,
			date_finished: wrap.data( 'now' )
		};

		apiRequest( 'v1/reading-log/update/' + wrap.data( 'log-id' ), args, 'POST' ).then( function( apiResponse ) {
			wrap.find( '.bdb-currently-reading-data' ).remove();
			wrap.find( '.bdb-currently-reading-rate-book' ).show();
		} ).catch( function( errorMessage ) {
			console.log( errorMessage );
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * DNF book
	 *
	 * @param e
	 * @returns {boolean}
	 */
	dnfBook: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_dnf_book ) ) {
			return false;
		}

		let button = $( this );

		spinButton( button );

		let wrap = button.closest( 'li' );

		let args = {
			date_finished: wrap.data( 'now' )
		};

		apiRequest( 'v1/reading-log/update/' + wrap.data( 'log-id' ), args, 'POST' ).then( function( apiResponse ) {
			wrap.find( '.bdb-currently-reading-data' ).remove();
			wrap.find( '.bdb-currently-reading-rate-book' ).show();
		} ).catch( function( errorMessage ) {
			console.log( errorMessage );
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Set the rating
	 *
	 * @param e
	 */
	setRating: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );

		let wrap = button.closest( 'li' );

		let args = {
			rating: wrap.find( '.bdb-currently-reading-rating' ).val()
		};

		apiRequest( 'v1/reading-log/update/' + wrap.data( 'log-id' ), args, 'POST' ).then( function( apiResponse ) {
			wrap.find( '.bdb-currently-reading-rate-book' ).empty().append( '<p>' + getStars( args.rating ) + '</p>' )
		} ).catch( function( errorMessage ) {
			console.log( errorMessage );
		} ).finally( function() {
			unspinButton( button );
		} );

	}

};

export { BDB_Dashboard_Widgets }