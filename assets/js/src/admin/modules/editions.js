/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from '../../utils';
import { dateLocalToUTC, dateUTCtoLocal } from "./dates";

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

		this.bookID    = jQuery( '#bdb-book-id' ).val();
		this.tableBody = jQuery( '#bdb-book-editions-list .wp-list-table tbody' );
		this.errorWrap = jQuery( '#bdb-editions-errors' );

		if ( ! this.tableBody.length || 'undefined' === typeof this.bookID || ! this.bookID ) {
			return;
		}

		jQuery( '#bdb-add-edition' ).on( 'click', this.toggleNewEditionFields );
		jQuery( '#bdb-submit-new-edition' ).on( 'click', this.addEdition );
		jQuery( document ).on( 'click', '.bdb-edition-toggle-editable', this.toggleEditableFields );
		jQuery( document ).on( 'click', '.bdb-update-edition', this.updateEdition );
		jQuery( document ).on( 'click', '.bdb-remove-edition', this.removeEdition );

		this.getEditions();

	},

	/**
	 * Get the editions
	 */
	getEditions: function() {

		apiRequest( 'v1/edition', { book_id: BDB_Editions.bookID, number: 50 }, 'GET' ).then( function( response ) {

			BDB_Editions.tableBody.empty();

			if ( 0 === response.length || 'undefined' === typeof response.length ) {
				BDB_Editions.tableBody.append( BDB_Editions.rowEmptyTemplate );
			} else {
				jQuery( '#bdb-book-editions-empty' ).remove();
				jQuery.each( response, function( key, edition ) {
					edition.date_acquired_formatted = dateUTCtoLocal( edition.date_acquired, 'display' );
					edition.date_acquired           = dateUTCtoLocal( edition.date_acquired );

					BDB_Editions.tableBody.append( BDB_Editions.rowTemplate( edition ) );
				} );
			}

			jQuery( document ).trigger( 'bdb_editions_loaded' );

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

		if ( 'undefined' !== typeof e ) {
			e.preventDefault();
		}

		jQuery( '#bdb-new-edition-fields' ).slideToggle();

	},

	/**
	 * Add a new edition
	 *
	 * @param e
	 */
	addEdition: function ( e ) {

		e.preventDefault();
		let button = jQuery( this );

		spinButton( button );
		BDB_Editions.errorWrap.empty().hide();

		let args = {
			book_id: BDB_Editions.bookID,
			isbn: jQuery( '#bdb-new-edition-isbn' ).val(),
			format: jQuery( '#bdb-new-edition-format' ).val(),
			date_acquired: dateLocalToUTC( jQuery( '#bdb-new-edition-date-acquired' ).val() ),
			source_id: jQuery( '#bdb-checkboxes-source-edition' ).find( 'input:checked' ).val(),
			signed: jQuery( '#bdb-new-edition-signed' ).prop( 'checked' ) ? 1 : 0
		};

		apiRequest( 'v1/edition/add', args, 'POST' ).then( function( apiResponse ) {

			apiResponse.date_acquired_formatted = dateUTCtoLocal( apiResponse.date_acquired, 'display' );
			apiResponse.date_acquired           = dateUTCtoLocal( apiResponse.date_acquired );

			jQuery( '#bdb-book-editions-empty' ).remove();
			BDB_Editions.tableBody.append( BDB_Editions.rowTemplate( apiResponse ) );

			// Wipe new field values.
			let newFieldsWrap = jQuery( '#bdb-new-edition-fields' );
			newFieldsWrap.find( 'input[type="text"]' ).val( '' );
			newFieldsWrap.find( 'input[type="checkbox"]' ).prop( 'checked', false );

			BDB_Editions.toggleNewEditionFields();

			// Add this edition to all dropdowns.
			addEditionToDropdown( apiResponse );

			// Trigger event.
			jQuery( document ).trigger( 'bdb_edition_added', apiResponse );

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

		let button = jQuery( this );
		let wrap = button.closest( 'tr' );

		wrap.find( '.bdb-table-display-value' ).hide();
		wrap.find( '.bdb-table-edit-value' ).show();

		button.removeClass( 'bdb-edition-toggle-editable' ).addClass( 'bdb-update-edition button-primary' ).text( bdbVars.save );

	},

	/**
	 * Update an edition
	 *
	 * @param e
	 */
	updateEdition: function ( e ) {

		e.preventDefault();

		let button = jQuery( this );

		spinButton( button );
		BDB_Editions.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		let args = {
			isbn: wrap.find( '.bdb-edition-isbn input' ).val(),
			format: wrap.find( '.bdb-edition-format select' ).val(),
			date_acquired: dateLocalToUTC( wrap.find( '.bdb-edition-date-acquired input' ).val() ),
			source_id: wrap.find( '.bdb-edition-source select' ).val(),
			signed: wrap.find( '.bdb-edition-signed input[type="checkbox"]' ).prop( 'checked' ) ? 1 : 0
		};

		apiRequest( 'v1/edition/update/' + wrap.data( 'id' ), args, 'POST' ).then( function( apiResponse ) {

			apiResponse.date_acquired_formatted = dateUTCtoLocal( apiResponse.date_acquired, 'display' );
			apiResponse.date_acquired           = dateUTCtoLocal( apiResponse.date_acquired );

			wrap.replaceWith( BDB_Editions.rowTemplate( apiResponse ) );

			// Update edition in dropdowns.
			addEditionToDropdown( apiResponse );

			jQuery( document ).trigger( 'bdb_edition_updated', apiResponse );

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

		let button = jQuery( this );

		spinButton( button );
		BDB_Editions.errorWrap.empty().hide();

		const wrap = button.closest( 'tr' );
		const editionID = wrap.data( 'id' );

		apiRequest( 'v1/edition/delete/' + editionID, {}, 'DELETE' ).then( function( apiResponse ) {
			wrap.remove();
			removeEditionFromDropdown( editionID );
		} ).catch( function( errorMessage ) {
			BDB_Editions.errorWrap.append( errorMessage ).show();
		} ).finally( function() {
			unspinButton( button );
		} );

	}

};

export { BDB_Editions }

/**
 *
 * Fill a provided <select> element with the Edition values
 *
 * @param {object} dropdown
 * @param {Array} editionsArray
 */
export function fillEditionsDropdown( dropdown, editionsArray ) {

	const selectedEdition = dropdown.data( 'selected' );

	dropdown.empty().append( '<option value="">' + bdbVars.none + '</option>' );

	jQuery.each( editionsArray, function( key, edition ) {
		let selected = edition.id == selectedEdition ? ' selected="selected"' : '';

		dropdown.append( '<option value="' + edition.id + '"' + selected + '>' + edition.isbn + ' - ' + edition.format_name + '</option>' );
	} );

}

/**
 * Add a new edition to the dropdowns
 *
 * @param {object} edition
 */
export function addEditionToDropdown( edition ) {
	jQuery( '.bdb-book-edition-list' ).each( function() {
		const dropdown = jQuery( this );
		const existingEdition = dropdown.find( 'option[value="' + edition.id + '"]' );

		if ( existingEdition.length ) {
			existingEdition.text( edition.isbn + ' - ' + edition.format_name );
		} else {
			dropdown.append( '<option value="' + edition.id + '">' + edition.isbn + ' - ' + edition.format_name + '</option>' );
		}
	} );
}

/**
 * Remove an edition from the dropdowns
 *
 * @param {number} editionID
 */
export function removeEditionFromDropdown( editionID ) {
	jQuery( '.bdb-book-edition-list' ).each( function() {
		const dropdown = jQuery( this );

		dropdown.find( 'option[value="' + editionID + '"]' ).remove();
	} );
}
