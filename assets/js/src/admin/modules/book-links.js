/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from '../../utils';

/**
 * Book Links
 */
var BDB_Book_Links = {

	bookID: 0,

	linkWrap: false,

	linkTemplateAdd: wp.template( 'bdb-book-link-add' ),

	linkTemplateEdit: wp.template( 'bdb-book-link-edit' ),

	errorWrap: '',

	/**
	 * Initialize
	 */
	init: function() {

		this.bookID = jQuery( '#bdb-book-id' ).val();
		this.linkWrap = jQuery( '#bdb-book-links' );
		this.errorWrap = jQuery( '#bdb-book-links-errors' );

		if ( ! this.linkWrap.length ) {
			return;
		}

		jQuery( '#bdb-new-purchase-link' ).on( 'keydown', 'input', this.clickOnEnter );
		jQuery( '#bdb-new-purchase-link' ).on( 'click', 'button', this.addLink );
		jQuery( document ).on( 'click', '.bdb-update-book-link', this.updateLink );
		jQuery( document ).on( 'click', '.bdb-remove-book-link', this.deleteLink );

		this.getLinks();

	},

	/**
	 * Get the links
	 */
	getLinks: function() {

		if ( ! this.bookID ) {
			return;
		}

		let args = {
			book_id: BDB_Book_Links.bookID,
			number: 50
		};

		apiRequest( 'v1/book-link', args, 'GET' ).then( function( apiResponse ) {

			BDB_Book_Links.linkWrap.empty();

			if ( 0 === apiResponse.length || 'undefined' === typeof apiResponse.length ) {
				// Do nothing.
			} else {
				jQuery.each( apiResponse, function( key, link ) {
					BDB_Book_Links.linkWrap.append( BDB_Book_Links.linkTemplateEdit( link ) );
				} );
			}

		} ).catch( function( error ) {
			BDB_Book_Links.errorWrap.empty().append( error ).show();
		} );

	},

	/**
	 * Trigger a button click when pressing `enter` inside an `<input>` field.
	 *
	 * @param e
	 */
	clickOnEnter: function ( e ) {

		if ( 13 === e.keyCode ) {
			e.preventDefault();

			jQuery( '#bdb-new-purchase-link' ).find( 'button' ).trigger( 'click' );
		}

	},

	/**
	 * Add a new link
	 *
	 * @param e
	 */
	addLink: function ( e ) {

		e.preventDefault();

		let button = jQuery( this );

		spinButton( button );
		BDB_Book_Links.errorWrap.empty().hide();

		if ( BDB_Book_Links.bookID ) {

			// Editing an existing book.

			let args = {
				book_id: BDB_Book_Links.bookID,
				retailer_id: jQuery( '#bdb-new-book-link-retailer' ).val(),
				url: jQuery( '#bdb-new-book-link-url' ).val()
			};

			apiRequest( 'v1/book-link/add', args, 'POST' ).then( function ( apiResponse ) {

				BDB_Book_Links.linkWrap.append( BDB_Book_Links.linkTemplateEdit( apiResponse ) );

				// Wipe field values.
				jQuery( '#bdb-new-purchase-link' ).find( 'input' ).val( '' );

				unspinButton( button );
			} ).catch( function ( errorMessage ) {
				BDB_Book_Links.errorWrap.append( errorMessage ).show();
				unspinButton( button );
			} );

		} else {

			// Adding a new book.

			let data = {
				id: jQuery( '.bdb-book-link' ).length,
				retailer_id: jQuery( '#bdb-new-book-link-retailer' ).val(),
				url: jQuery( '#bdb-new-book-link-url' ).val()
			};

			BDB_Book_Links.linkWrap.append( BDB_Book_Links.linkTemplateAdd( data ) );

			// Wipe field values.
			jQuery( '#bdb-new-purchase-link' ).find( 'input' ).val( '' );

			unspinButton( button );

		}

	},

	/**
	 * Update a link
	 *
	 * @param e
	 */
	updateLink: function ( e ) {

		e.preventDefault();

		let button = jQuery( this );

		spinButton( button );
		BDB_Book_Links.errorWrap.empty().hide();

		let wrap = button.closest( '.bdb-book-link' );

		let args = {
			retailer_id: wrap.find( '.bdb-book-link-retailer' ).val(),
			url: wrap.find( '.bdb-book-link-url' ).val()
		};

		apiRequest( 'v1/book-link/update/' + wrap.data( 'id' ), args, 'POST' ).then( function( apiResponse ) {
			unspinButton( button );
		} ).catch( function( errorMessage ) {
			BDB_Book_Links.errorWrap.append( errorMessage ).show();
			unspinButton( button );
		} );

	},

	/**
	 * Delete a link
	 *
	 * @param e
	 */
	deleteLink: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_delete_book_link ) ) {
			return false;
		}

		let button = jQuery( this );

		spinButton( button );
		BDB_Book_Links.errorWrap.empty().hide();

		let wrap = button.closest( '.bdb-book-link' );

		if ( BDB_Book_Links.bookID ) {
			apiRequest( 'v1/book-link/delete/' + wrap.data( 'id' ), {}, 'DELETE' ).then( function ( apiResponse ) {
				wrap.remove();
			} ).catch( function ( errorMessage ) {
				BDB_Book_Links.errorWrap.append( errorMessage ).show();
				unspinButton( button );
			} );
		} else {
			wrap.remove();
		}

	}

};

export { BDB_Book_Links }
