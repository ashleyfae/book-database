/* global $, bdbVars, wp */

import flatpickr from "flatpickr";

/**
 * Datepicker
 */
var BDB_Datepicker = {

	/**
	 * Initialize
	 */
	init: function() {

		if ( $( '.bdb-datepicker' ).length > 0 ) {
			this.initDatepickers();
		}

		$( document ).on( 'click', '.bdb-edit-row-with-datepicker', this.maybeAddDatepicker );

	},

	/**
	 * Create datepickers
	 *
	 * altInput is disabled because when it's enabled it breaks the ability to
	 * manually delete the input value and have that reflected in the DOM.
	 * @link https://github.com/nosegraze/book-database/issues/194
	 * @link https://github.com/flatpickr/flatpickr/issues/1910
	 *
	 * @param {Element} element
	 */
	createDatepicker: function ( element ) {

		let config = {
			allowInput: true,
			dateFormat: 'Y-m-d'
		};

		if ( element.classList.contains( 'bdb-timepicker' ) ) {
			config.enableTime = true;
			config.dateFormat = 'Y-m-d H:i';
			config.altFormat = 'F J, Y, h:i K';
		}

		flatpickr( element, config );

	},

	/**
	 * Create datepickers for all elements already on the page
	 */
	initDatepickers: function() {
		document.querySelectorAll( '.bdb-datepicker' ).forEach( function( element ) {
			BDB_Datepicker.createDatepicker( element );
		} );
	},

	/**
	 * When editing a table row item, find all datepickers within it and initialize them.
	 *
	 * @param e
	 */
	maybeAddDatepicker: function ( e ) {

		this.closest( 'tr' ).querySelectorAll( '.bdb-datepicker' ).forEach( function( element ) {
			const fp = element._flatpickr;

			if ( 'undefined' === typeof fp ) {
				BDB_Datepicker.createDatepicker( element );
			}
		} );

	}

};

export { BDB_Datepicker }