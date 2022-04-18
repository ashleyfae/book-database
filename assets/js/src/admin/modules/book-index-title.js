/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from '../../utils';

var BDB_Book_Index_Title = {

	bookTitleField: false,

	indexTitleSelect: false,

	indexTitleCustomField: false,

	/**
	 * Initialize
	 */
	init: function() {

		this.bookTitleField = jQuery( '#bdb-book-title' );
		this.indexTitleSelect = jQuery( '#bdb-book-index-title' );
		this.indexTitleCustomField = jQuery( '#bdb-book-index-title-custom' );

		this.indexTitleSelect.on( 'change', this.toggleCustomIndexTitle ).trigger( 'change' );
		this.bookTitleField.on( 'keyup', this.writeOriginalTitle );
		this.bookTitleField.on( 'blur', this.populateIndexTitles );

	},

	/**
	 * Show the "Custom" box if "Custom" is selected. Otherwise, hide it.
	 */
	toggleCustomIndexTitle: function () {

		let selectedIndexTitle = jQuery( this ).val();

		if ( 'custom' === selectedIndexTitle ) {
			BDB_Book_Index_Title.indexTitleCustomField.slideDown().css( 'display', 'block' );
		} else {
			BDB_Book_Index_Title.indexTitleCustomField.slideUp();
		}

	},

	/**
	 * Copies the contents of the original "Book Title" field to the "original" index title option.
	 */
	writeOriginalTitle: function () {
		BDB_Book_Index_Title.indexTitleSelect.find( 'option[value="original"]' ).text( jQuery( this ).val() );
	},

	/**
	 * Create an index-friendly version of the entered book title and insert it as an
	 * option in the <select> dropdown.
	 */
	populateIndexTitles: function () {

		let args = {
			title: jQuery( this ).val()
		};

		apiRequest( 'v1/book/index-title', args, 'GET' ).then( function( response ) {
			BDB_Book_Index_Title.indexTitleSelect.find( 'option[value="original"]' ).after( '<option value="' + response + '">' + response + '</option>' );
		} ).catch( function( error ) {
			console.log( 'Index title generation error', error );
		} );

	}

};

export { BDB_Book_Index_Title }
