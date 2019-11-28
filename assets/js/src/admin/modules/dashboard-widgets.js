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
		$( '.bdb-currently-reading-progress-unit-choices' ).on( 'click', 'a', this.setUnit );
		$( '.bdb-currently-reading-widget-save-progress' ).on( 'click', this.saveProgress );
		$( '.bdb-currently-reading-set-progress-wrap' ).on( 'keydown', 'input', this.saveProgressOnEnter );
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

		let wrap = $( this ).closest( 'li' );

		wrap.find( '.bdb-currently-reading-set-progress-wrap' ).slideToggle();

	},

	/**
	 * Set the unit to use for updating progress
	 *
	 * @param e
	 */
	setUnit: function ( e ) {

		e.preventDefault();

		let wrap = $( this ).closest( '.bdb-currently-reading-set-progress-wrap' ),
			unit = $( this ).data( 'unit' );

		wrap.find( '.bdb-currently-reading-progress-unit-choices a' ).removeClass( 'bdb-currently-reading-progress-unit-selected' );
		$( this ).addClass( 'bdb-currently-reading-progress-unit-selected' );

		if ( 'page' === unit ) {
			wrap.find( '.bdb-currently-reading-unit-percentage-wrap' ).hide();
			wrap.find( '.bdb-currently-reading-unit-pages-wrap' ).show();
		} else {
			wrap.find( '.bdb-currently-reading-unit-percentage-wrap' ).show();
			wrap.find( '.bdb-currently-reading-unit-pages-wrap' ).hide();
		}

	},

	/**
	 * Save the new progress
	 *
	 * @param e
	 */
	saveProgress: function ( e ) {

		e.preventDefault();

		let button = $( this ),
			wrap = button.closest( 'li' ),
			logID = wrap.data( 'log-id' ),
			unit = wrap.find( '.bdb-currently-reading-progress-unit-selected' ).data( 'unit' ),
			percentage = 0,
			readablePercentage,
			progressWrap = wrap.find( '.bdb-currently-reading-progress-bar' ),
			progressNumber = wrap.find( '.bdb-currently-reading-progress-number' );

		spinButton( button );

		// Figure out the percentage.
		if ( 'page' === unit ) {
			let pageField = wrap.find( '.bdb-currently-reading-unit-page' ),
				maxPages = parseInt( pageField.data( 'max' ) ),
				currentPage = parseInt( pageField.val() );

			if ( maxPages > 0 ) {
				percentage = currentPage / maxPages;
				readablePercentage = Math.round( percentage * 100 );
			}
		} else {
			let percentageField = wrap.find( '.bdb-currently-reading-unit-percentage' );

			readablePercentage = parseFloat( percentageField.val() );

			if ( readablePercentage > 0 ) {
				percentage = readablePercentage / 100;
			}
		}

		let args = {
			percentage_complete: percentage
		};

		apiRequest( 'v1/reading-log/update/' + logID, args, 'POST' ).then( function( apiResponse ) {
			progressWrap.css( 'width', readablePercentage + '%' );
			progressNumber.text( readablePercentage + '%' );
		} ).catch( function( errorMessage ) {
			console.log( errorMessage );
		} ).finally( function() {
			unspinButton( button );
			wrap.find( '.bdb-currently-reading-set-progress-wrap' ).slideUp();
		} );

	},

	/**
	 * Trigger progress saving when clicking "enter"
	 *
	 * @param e
	 */
	saveProgressOnEnter: function ( e ) {

		if ( 13 === e.keyCode ) {
			e.preventDefault();

			$( this ).closest( 'li' ).find( '.bdb-currently-reading-widget-save-progress' ).trigger( 'click' );
		}

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