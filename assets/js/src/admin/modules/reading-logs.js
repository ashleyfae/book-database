/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from 'utils';
import { dateLocalToUTC, dateUTCtoLocal } from "./dates";

/**
 * Editions
 */
var BDB_Reading_Logs = {

	bookID: 0,

	userID: 0,

	tableBody: false,

	rowTemplate: wp.template( 'bdb-reading-logs-table-row' ),

	rowEmptyTemplate: wp.template( 'bdb-reading-logs-table-row-empty' ),

	errorWrap: '',

	userFilter: false,

	/**
	 * Initialize
	 */
	init: function() {

		this.bookID    = $( '#bdb-book-id' ).val();
		this.userID    = $( '#bdb-book-reading-logs-list' ).data( 'user-id' );
		this.tableBody = $( '#bdb-book-reading-logs-list .wp-list-table tbody' );
		this.errorWrap = $( '#bdb-reading-logs-errors' );
		this.userFilter = $( '#bdb-book-reading-logs-user-filter' );

		if ( ! this.tableBody.length || 'undefined' === typeof this.bookID || ! this.bookID ) {
			return;
		}

		$( '#bdb-add-reading-log' ).on( 'click', this.toggleNewLogFields );
		$( '#bdb-submit-new-reading-log' ).on( 'click', this.addLog );
		$( document ).on( 'click', '.bdb-reading-log-toggle-editable', this.toggleEditableFields );
		$( document ).on( 'click', '.bdb-update-reading-log', this.updateLog );
		$( document ).on( 'click', '.bdb-remove-reading-log', this.removeLog );

		this.userFilter.on( 'change', this.getLogs );
		this.userFilter.trigger( 'change' );

	},

	/**
	 * Set up the object for use in the template
	 *
	 * - Convert UTC dates to local
	 * - Set up the percentage for display (0 - 100)
	 *
	 * @param {object} readingLog
	 * @returns {object}
	 */
	shapeObject: function( readingLog ) {
		readingLog.date_started_formatted  = dateUTCtoLocal( readingLog.date_started, 'display' );
		readingLog.date_started            = dateUTCtoLocal( readingLog.date_started );
		readingLog.date_finished_formatted = dateUTCtoLocal( readingLog.date_finished, 'display' );
		readingLog.date_finished           = dateUTCtoLocal( readingLog.date_finished );
		readingLog.percentage_complete     = ( readingLog.percentage_complete * 100 ).toFixed( 0 );
		readingLog.rating                  = null === readingLog.rating ? null : parseFloat( readingLog.rating );
		readingLog.rating_formatted        = null === readingLog.rating ? null : parseFloat( readingLog.rating ) + ' ' + bdbVars.stars;

		return readingLog;
	},

	/**
	 * Get the reading logs
	 */
	getLogs: function() {

		let args = {
			book_id: BDB_Reading_Logs.bookID,
			number: 50
		};

		if ( $( '#bdb-book-reading-logs-user-filter' ).prop( 'checked' ) ) {
			args.user_id = BDB_Reading_Logs.userID;
		}

		apiRequest( 'v1/reading-log', args, 'GET' ).then( function( response ) {

			BDB_Reading_Logs.tableBody.empty();

			if ( 0 === response.length || 'undefined' === typeof response.length ) {
				BDB_Reading_Logs.tableBody.append( BDB_Reading_Logs.rowEmptyTemplate );
			} else {
				$( '#bdb-book-reading-logs-empty' ).remove();
				$.each( response, function( key, readingLog ) {
					readingLog = BDB_Reading_Logs.shapeObject( readingLog );

					BDB_Reading_Logs.tableBody.append( BDB_Reading_Logs.rowTemplate( readingLog ) );
				} );
			}

			$( document ).trigger( 'bdb_reading_logs_loaded' );

		} ).catch( function( error ) {
			BDB_Reading_Logs.errorWrap.empty().append( error ).show();
		} );

	},

	/**
	 * Toggle the new log fields
	 *
	 * @param e
	 */
	toggleNewLogFields: function ( e ) {

		if ( 'undefined' !== typeof e ) {
			e.preventDefault();
		}

		$( '#bdb-new-reading-log-fields' ).slideToggle();

	},

	/**
	 * Add a new reading log
	 *
	 * @param e
	 */
	addLog: function ( e ) {

		e.preventDefault();
		let button = $( this );

		spinButton( button );
		BDB_Reading_Logs.errorWrap.empty().hide();

		let percentage = $( '#bdb-new-log-percent-complete' ).val();
		if ( '' !== percentage && percentage > 0 ) {
			percentage = percentage / 100;
		} else {
			percentage = 0;
		}

		let args = {
			book_id: BDB_Reading_Logs.bookID,
			user_id: BDB_Reading_Logs.userID,
			date_started: dateLocalToUTC( $( '#bdb-new-log-start-date' ).val() ),
			date_finished: dateLocalToUTC( $( '#bdb-new-log-end-date' ).val() ),
			percentage_complete: percentage,
			rating: $( '#bdb-new-log-rating' ).val()
		};

		apiRequest( 'v1/reading-log/add', args, 'POST' ).then( function( apiResponse ) {
			apiResponse = BDB_Reading_Logs.shapeObject( apiResponse );

			$( '#bdb-book-reading-logs-empty' ).remove();

			BDB_Reading_Logs.tableBody.append( BDB_Reading_Logs.rowTemplate( apiResponse ) );

			// Wipe new field values.
			let newFieldsWrap = $( '#bdb-new-reading-log-fields' );
			newFieldsWrap.find( 'input[type="text"]' ).val( '' );
			newFieldsWrap.find( 'input[type="checkbox"]' ).prop( 'checked', false );

			BDB_Reading_Logs.toggleNewLogFields();

			// Trigger event
			$( document ).trigger( 'bdb_reading_log_added', apiResponse );

		} ).catch( function( errorMessage ) {
			BDB_Reading_Logs.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Toggle the editable reading log fields
	 *
	 * @param e
	 */
	toggleEditableFields: function ( e ) {

		e.preventDefault();

		let button = $( this );
		let wrap = button.closest( 'tr' );

		wrap.find( '.bdb-table-display-value' ).hide();
		wrap.find( '.bdb-table-edit-value' ).show();

		button.removeClass( 'bdb-reading-log-toggle-editable' ).addClass( 'bdb-update-reading-log button-primary' ).text( bdbVars.save );

	},

	/**
	 * Update a reading log
	 *
	 * @param e
	 */
	updateLog: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );
		BDB_Reading_Logs.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		let percentage = wrap.find( '.bdb-reading-log-percentage-complete input' ).val();
		if ( '' !== percentage && percentage > 0 ) {
			percentage = percentage / 100;
		} else {
			percentage = 0;
		}

		let args = {
			date_started: dateLocalToUTC( wrap.find( '.bdb-reading-log-date-started input' ).val() ),
			date_finished: dateLocalToUTC( wrap.find( '.bdb-reading-log-date-finished input' ).val() ),
			user_id: wrap.find( '.bdb-reading-log-user-id input' ).val(),
			percentage_complete: percentage,
			rating: wrap.find( '.bdb-reading-log-rating select' ).val()
		};

		apiRequest( 'v1/reading-log/update/' + wrap.data( 'id' ), args, 'POST' ).then( function( apiResponse ) {
			apiResponse = BDB_Reading_Logs.shapeObject( apiResponse );
			wrap.replaceWith( BDB_Reading_Logs.rowTemplate( apiResponse ) );
			$( document ).trigger( 'bdb_reading_log_updated', apiResponse );
		} ).catch( function( errorMessage ) {
			BDB_Reading_Logs.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Delete an edition
	 *
	 * @param e
	 * @returns {boolean}
	 */
	removeLog: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_delete_reading_log ) ) {
			return false;
		}

		let button = $( this );

		spinButton( button );
		BDB_Reading_Logs.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		apiRequest( 'v1/reading-log/delete/' + wrap.data( 'id' ), {}, 'DELETE' ).then( function( apiResponse ) {
			wrap.remove();
		} ).catch( function( errorMessage ) {
			BDB_Reading_Logs.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	}

};

export { BDB_Reading_Logs }