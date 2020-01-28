/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from 'utils';
import { dateLocalToUTC, dateUTCtoLocal } from "./dates";

/**
 * Editions
 */
var BDB_Reading_Logs = {

	bookID: 0,

	userID: 0,

	maxPages: 0,

	tableBody: false,

	rowTemplate: wp.template( 'bdb-reading-logs-table-row' ),

	rowEmptyTemplate: wp.template( 'bdb-reading-logs-table-row-empty' ),

	errorWrap: '',

	userFilter: false,

	editions: [],

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

		this.maxPages = $( '#bdb-book-pages' ).val();
		this.loadEditions();
		$( '#bdb-add-reading-log' ).on( 'click', this.toggleNewLogFields );
		$( '#bdb-submit-new-reading-log' ).on( 'click', this.addLog );
		$( document ).on( 'click', '.bdb-reading-log-toggle-editable', this.toggleEditableFields );
		$( document ).on( 'click', '.bdb-reading-log-percentage-complete .bdb-input-suffix', this.toggleCompleteUnit );
		$( document ).on( 'click', '.bdb-update-reading-log', this.updateLog );
		$( document ).on( 'click', '.bdb-remove-reading-log', this.removeLog );

		$( document ).on( 'bdb_reading_log_added', this.fillLogEditions );

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
	 * Load editions
	 *
	 * @returns {Promise}
	 */
	loadEditions: function () {
		return apiRequest( 'v1/edition', { book_id: BDB_Reading_Logs.bookID, number: 50 }, 'GET' );
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

		BDB_Reading_Logs.loadEditions().then( function( editions ) {
			BDB_Reading_Logs.editions = editions;

			// Populate editions in "New Log".
			if ( BDB_Reading_Logs.editions.length ) {
				const selectEditionWrap = $( '#bdb-new-log-edition-id-wrap' );
				const selectEditionDropdown = $( '#bdb-new-log-edition-id' );

				selectEditionDropdown.empty().append( '<option value="">' + bdbVars.none + '</option>' );

				$.each( BDB_Reading_Logs.editions, function( key, edition ) {
					selectEditionDropdown.append( '<option value="' + edition.id + '">' + edition.isbn + ' - ' + edition.format_name + '</option>' );
				} );

				selectEditionWrap.show();
			}

			return apiRequest( 'v1/reading-log', args, 'GET' );
		} ).then( function( response ) {

			BDB_Reading_Logs.tableBody.empty();

			if ( 0 === response.length || 'undefined' === typeof response.length ) {
				BDB_Reading_Logs.tableBody.append( BDB_Reading_Logs.rowEmptyTemplate );
			} else {
				$( '#bdb-book-reading-logs-empty' ).remove();
				$.each( response, function( key, readingLog ) {
					readingLog = BDB_Reading_Logs.shapeObject( readingLog );

					BDB_Reading_Logs.tableBody.append( BDB_Reading_Logs.rowTemplate( readingLog ) );
				} );

				BDB_Reading_Logs.tableBody.find( '.bdb-book-edition-list' ).each( function() {
					BDB_Reading_Logs.fillLogEditions( $( this ) );
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
	 * Fill the log editions for a specific <select> dropdown.
	 *
	 * @param editionList
	 */
	fillLogEditions: function ( editionList ) {

		console.log( 'Fill log editions list', editionList );

		const selectedEdition = editionList.data( 'selected' );

		editionList.empty().append( '<option value="">' + bdbVars.none + '</option>' );

		$.each( BDB_Reading_Logs.editions, function( key, edition ) {
			let selected = edition.id == selectedEdition ? ' selected="selected"' : '';

			editionList.append( '<option value="' + edition.id + '"' + selected + '>' + edition.isbn + ' - ' + edition.format_name + '</option>' );
		} );

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

		const selectedEditionID = $( '#bdb-new-log-edition-id' ).val();

		let args = {
			book_id: BDB_Reading_Logs.bookID,
			edition_id: selectedEditionID.length > 0 ? selectedEditionID : null,
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

			const editionDropdown = $( '#bdb-reading-log-edition-id-' + apiResponse.id );
			console.log( 'Edition Dropdown', editionDropdown );
			if ( editionDropdown.length ) {
				BDB_Reading_Logs.fillLogEditions( editionDropdown );
			}

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
	 * Toggle the fields for the chosen unit (page vs percentage)
	 *
	 * @param e
	 */
	toggleCompleteUnit: function ( e ) {

		e.preventDefault();

		let wrap = $( this ).closest( '.bdb-reading-log-percentage-complete' );
		let type = 'percentage';

		if ( $( this ).hasClass( 'bdb-input-suffix-page' ) ) {
			type = 'page';
		}

		// Change which one is selected.
		wrap.find( '.bdb-input-suffix' ).removeClass( 'bdb-input-suffix-selected' );
		$( this ).addClass( 'bdb-input-suffix-selected' );

		// Show/hide relevant inputs.
		if ( 'page' === type ) {
			wrap.find( '.bdb-reading-log-percentage-complete-wrap' ).hide();
			wrap.find( '.bdb-reading-log-page-wrap' ).show();
		} else {
			wrap.find( '.bdb-reading-log-percentage-complete-wrap' ).show();
			wrap.find( '.bdb-reading-log-page-wrap' ).hide();
		}

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

		// Figure out if we're working with page numbers or percentages.
		let percentage = 0,
			unitType = 'percentage',
			selectedSuffix = wrap.find( '.bdb-input-suffix-selected' );

		if ( selectedSuffix.hasClass( 'bdb-input-suffix-page' ) ) {
			unitType = 'page';
		}

		if ( 'page' === unitType ) {
			// Page number.
			let pageNumber = wrap.find( '.bdb-reading-log-page-wrap input' ).val();
			percentage = ( BDB_Reading_Logs.maxPages > 0 ) ? pageNumber / BDB_Reading_Logs.maxPages : 0;
		} else {
			// Percentage.
			percentage = wrap.find( '.bdb-reading-log-percentage-complete-wrap input' ).val();
			if ( '' !== percentage && percentage > 0 ) {
				percentage = percentage / 100;
			} else {
				percentage = 0;
			}
		}

		const selectedEditionID = wrap.find( '.bdb-book-edition-list' ).val();

		let args = {
			date_started: dateLocalToUTC( wrap.find( '.bdb-reading-log-date-started input' ).val() ),
			date_finished: dateLocalToUTC( wrap.find( '.bdb-reading-log-date-finished input' ).val() ),
			edition_id: selectedEditionID.length > 0 ? selectedEditionID : null,
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