/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from 'utils';

/**
 * Book Links
 */
var BDB_Book_Links = {

	bookID: 0,

	linkWrap: false,

	linkTemplate: wp.template( 'bdb-book-link' ),

	errorWrap: '',

	/**
	 * Initialize
	 */
	init: function() {

		this.bookID = $( '#bdb-book-id' ).val();
		this.linkWrap = $( '#bdb-book-links' );
		this.errorWrap = $( '#bdb-book-links-errors' );

		if ( ! this.linkWrap.length || 'undefined' === typeof this.bookID ) {
			return;
		}

		$( '#bdb-new-purchase-link' ).on( 'keydown', 'input', this.clickOnEnter );
		$( '#bdb-new-purchase-link' ).on( 'click', 'button', this.addLink );
		$( document ).on( 'click', '.bdb-update-book-link', this.updateLink );
		$( document ).on( 'click', '.bdb-remove-book-link', this.deleteLink );

		this.getLinks();

	},

	/**
	 * Get the links
	 */
	getLinks: function() {

		let args = {
			book_id: BDB_Book_Links.bookID,
			number: 50
		};

		apiRequest( 'v1/book-link', args, 'GET' ).then( function( apiResponse ) {

			BDB_Book_Links.linkWrap.empty();

			if ( 0 === apiResponse.length || 'undefined' === typeof apiResponse.length ) {
				// Do nothing.
			} else {
				$.each( apiResponse, function( key, link ) {
					BDB_Book_Links.linkWrap.append( BDB_Book_Links.linkTemplate( link ) );
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

			$( '#bdb-new-purchase-link' ).find( 'button' ).trigger( 'click' );
		}

	},

	/**
	 * Add a new link
	 *
	 * @param e
	 */
	addLink: function ( e ) {

		e.preventDefault();

		let button = $( this );

		spinButton( button );
		BDB_Book_Links.errorWrap.empty().hide();

		let args = {
			book_id: BDB_Book_Links.bookID,
			retailer_id: $( '#bdb-new-book-link-retailer' ).val(),
			url: $( '#bdb-new-book-link-url' ).val()
		};

		apiRequest( 'v1/book-link/add', args, 'POST' ).then( function( apiResponse ) {

			BDB_Book_Links.linkWrap.append( BDB_Book_Links.linkTemplate( apiResponse ) );

			// Wipe field values.
			$( '#bdb-new-purchase-link' ).find( 'input' ).val( '' );

			unspinButton( button );
		} ).catch( function( errorMessage ) {
			BDB_Book_Links.errorWrap.append( errorMessage ).show();
			unspinButton( button );
		} );

	},

	/**
	 * Update a link
	 *
	 * @param e
	 */
	updateLink: function ( e ) {

		e.preventDefault();

		let button = $( this );

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

		let button = $( this );

		spinButton( button );
		BDB_Book_Links.errorWrap.empty().hide();

		let wrap = button.closest( '.bdb-book-link' );

		apiRequest( 'v1/book-link/delete/' + wrap.data( 'id' ), {}, 'DELETE' ).then( function( apiResponse ) {
			wrap.remove();
		} ).catch( function( errorMessage ) {
			BDB_Book_Links.errorWrap.append( errorMessage ).show();
			unspinButton( button );
		} );

	}

};

export { BDB_Book_Links }