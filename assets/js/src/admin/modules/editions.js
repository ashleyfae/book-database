/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from 'utils';

/**
 * Editions
 */
var BDB_Editions = {

	bookID: 0,

	tableBody: false,

	rowTemplate: wp.template( 'bdb-editions-table-row' ),

	rowEmptyTemplate: wp.template( 'bdb-editions-table-row-empty' ),

	errorWrap: '',

	/**
	 * Initialize
	 */
	init: function() {

		this.bookID    = $( '#bdb-book-id' ).val();
		this.tableBody = $( '#bdb-book-editions-list .wp-list-table tbody' );
		this.errorWrap = $( '#bdb-editions-errors' );

		if ( ! this.tableBody.length || 'undefined' === typeof this.bookID || ! this.bookID ) {
			return;
		}

		$( '#bdb-add-edition' ).on( 'click', this.toggleNewEditionFields );
		$( '#bdb-submit-new-edition' ).on( 'click', this.addEdition );
		$( document ).on( 'click', '.bdb-edition-toggle-editable', this.toggleEditableFields );
		$( document ).on( 'click', '.bdb-update-edition', this.updateEdition );
		$( document ).on( 'click', '.bdb-remove-edition', this.removeEdition );

		this.getEditions();

	},

	/**
	 * Get the editions
	 */
	getEditions: function() {

		apiRequest( 'v1/edition', { book_id: BDB_Editions.bookID }, 'GET' ).then( function( response ) {

			BDB_Editions.tableBody.empty();

			if ( 0 === response.length || 'undefined' === typeof response.length ) {
				BDB_Editions.tableBody.append( BDB_Editions.rowEmptyTemplate );
			} else {
				$( '#bdb-book-editions-empty' ).remove();
				$.each( response, function( key, edition ) {
					BDB_Editions.tableBody.append( BDB_Editions.rowTemplate( edition ) );
				} );
			}

		} ).catch( function( error ) {
			BDB_Editions.errorWrap.empty().append( error ).show();
		} );

	},

	/**
	 * Toggle the new edition fields
	 *
	 * @param e
	 */
	toggleNewEditionFields: function ( e ) {

		e.preventDefault();

		$( '#bdb-new-edition-fields' ).slideToggle();

	},

	/**
	 * Add a new edition
	 *
	 * @param e
	 */
	addEdition: function ( e ) {

		e.preventDefault();
		let button = $( this );

		spinButton( button );
		BDB_Editions.errorWrap.empty().hide();

		let args = {
			book_id: $( '#bdb-book-id' ).val(),
			isbn: $( '#bdb-new-edition-isbn' ).val(),
			format: $( '#bdb-new-edition-format' ).val(),
			date_acquired: $( '#bdb-new-edition-date-acquired' ).val(),
			source_id: $( '#bdb-checkboxes-source-edition' ).find( 'input:checked' ).val(),
			signed: $( '#bdb-new-edition-signed' ).prop( 'checked' )
		};

		apiRequest( 'v1/utility/convert-date', { date: args.date_acquired }, 'POST' ).then( function( dateResponse ) {
			args.date_acquired = dateResponse;

			console.log(args);

			return apiRequest( 'v1/edition/add', args, 'POST' );
		} ).then( function( response ) {

			$( '#bdb-book-editions-empty' ).remove();
			BDB_Editions.tableBody.append( BDB_Editions.rowTemplate( response ) );

			// Wipe new field values.
			let newFieldsWrap = $( '#bdb-new-edition-fields' );
			newFieldsWrap.find( 'input[type="text"]' ).val( '' );
			newFieldsWrap.find( 'input[type="checkbox"]' ).prop( 'checked', false );

		} ).catch( function( errorMessage ) {
			BDB_Editions.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Toggle the editable edition fields
	 *
	 * @param e
	 */
	toggleEditableFields: function ( e ) {

		e.preventDefault();

		let button = $( this );
		let wrap = button.closest( 'tr' );

		wrap.find( '.bdb-table-display-value' ).hide();
		wrap.find( '.bdb-table-edit-value' ).show();

		button.removeClass( 'bdb-edition-toggle-editable' ).addClass( 'bdb-update-edition button-primary' ).text( 'Save' );

	},

	/**
	 * Update an edition
	 *
	 * @param e
	 */
	updateEdition: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );
		BDB_Editions.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		let args = {
			isbn: wrap.find( '.bdb-edition-isbn input' ).val(),
			format: wrap.find( '.bdb-edition-format select' ).val(),
			date_acquired: wrap.find( '.bdb-edition-date-acquired input' ).val(),
			source_id: wrap.find( '.bdb-edition-source select' ).val(),
			signed: wrap.find( '.bdb-edition-signed input[type="checkbox"]' ).prop( 'checked' )
		};

		apiRequest( 'v1/utility/convert-date', { date: args.date_acquired }, 'POST' ).then( function( dateResponse ) {
			args.date_acquired = dateResponse;

			return apiRequest( 'v1/edition/update/' + wrap.data( 'id' ), args, 'POST' );
		} ).then( function( apiResponse ) {
			wrap.replaceWith( BDB_Editions.rowTemplate( apiResponse ) );
		} ).catch( function( errorMessage ) {
			BDB_Editions.errorWrap.append( errorMessage ).show();
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
	removeEdition: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_delete_edition ) ) {
			return false;
		}

		let button = $( this );

		spinButton( button );
		BDB_Editions.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		apiRequest( 'v1/edition/delete/' + wrap.data( 'id' ), {}, 'DELETE' ).then( function( apiResponse ) {
			wrap.remove();
		} ).catch( function( errorMessage ) {
			BDB_Editions.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	}

};

export { BDB_Editions }