/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from '../../utils';

/**
 * Book Taxonomies
 */
var BDB_Book_Taxonomies = {

	tableBody: false,

	rowTemplate: wp.template( 'bdb-taxonomies-table-row' ),

	rowEmptyTemplate: wp.template( 'bdb-taxonomies-table-row-empty' ),

	errorWrap: '',

	/**
	 * Initialize
	 */
	init: function() {

		this.tableBody = jQuery( '#bdb-book-taxonomies tbody' );
		this.errorWrap = jQuery( '#bdb-book-taxonomies-errors' );

		if ( ! this.tableBody.length ) {
			return;
		}

		jQuery( '#bdb-new-book-taxonomy-name' ).on( 'keyup', this.generateSlug );
		jQuery( '#bdb-new-book-taxonomy-fields' ).on( 'keydown', 'input', this.clickOnEnter );
		jQuery( '#bdb-new-book-taxonomy-fields' ).on( 'click', '.button-primary', this.addTaxonomy );
		jQuery( document ).on( 'click', '.bdb-update-book-taxonomy', this.updateTaxonomy );
		jQuery( document ).on( 'click', '.bdb-remove-book-taxonomy', this.deleteTaxonomy );

		this.getTaxonomies();

	},

	/**
	 * Get the list of taxonomies
	 */
	getTaxonomies: function() {

		apiRequest( 'v1/taxonomy', { number: 50 }, 'GET' ).then( function( response ) {

			BDB_Book_Taxonomies.tableBody.empty();

			if ( 0 === response.length || 'undefined' === typeof response.length ) {
				BDB_Book_Taxonomies.tableBody.append( BDB_Book_Taxonomies.rowEmptyTemplate );
			} else {
				jQuery( '#bdb-book-taxonomies-empty' ).remove();
				jQuery.each( response, function( key, taxonomy ) {
					BDB_Book_Taxonomies.tableBody.append( BDB_Book_Taxonomies.rowTemplate( taxonomy ) );
				} );
			}

		} ).catch( function( error ) {
			BDB_Book_Taxonomies.errorWrap.empty().append( error ).show();
		} );

	},

	/**
	 * Automatically generate a slug from the name
	 *
	 * @param e
	 */
	generateSlug: function ( e ) {

		let name = jQuery( '#bdb-new-book-taxonomy-name' ).val();
		let slug = name.toLowerCase().replace( /[^a-z0-9_\-]/g, '' );

		jQuery( '#bdb-new-book-taxonomy-slug' ).val( slug );

	},

	/**
	 * Trigger a button click when pressing `enter` inside an `<input>` field.
	 *
	 * @param e
	 */
	clickOnEnter: function ( e ) {

		if ( 13 === e.keyCode ) {
			e.preventDefault();

			jQuery( '#bdb-new-book-taxonomy-fields' ).find( 'button' ).trigger( 'click' );
		}

	},

	/**
	 * Add a new taxonomy
	 *
	 * @param e
	 */
	addTaxonomy: function ( e ) {

		e.preventDefault();

		let button = jQuery( this );

		spinButton( button );
		BDB_Book_Taxonomies.errorWrap.empty().hide();

		let args = {
			name: jQuery( '#bdb-new-book-taxonomy-name' ).val(),
			slug: jQuery( '#bdb-new-book-taxonomy-slug' ).val(),
			format: jQuery( '#bdb-new-book-taxonomy-format' ).val()
		};

		BDB_Book_Taxonomies.checkRequiredFields( args ).then( function( requirementsResponse ) {
			return apiRequest( 'v1/taxonomy/add', args, 'POST' );
		} ).then( function( apiResponse ) {
			jQuery( '#bdb-book-taxonomies-empty' ).remove();

			BDB_Book_Taxonomies.tableBody.append( BDB_Book_Taxonomies.rowTemplate( apiResponse ) );

			// Wipe field values.
			jQuery( '#bdb-new-book-taxonomy-fields' ).find( 'input' ).val( '' );

			unspinButton( button );
		} ).catch( function( errorMessage ) {
			BDB_Book_Taxonomies.errorWrap.append( errorMessage ).show();
			unspinButton( button );
		} );

	},

	/**
	 * Update a taxonomy
	 *
	 * @param e
	 */
	updateTaxonomy: function ( e ) {

		e.preventDefault();

		let button = jQuery( this );

		spinButton( button );
		BDB_Book_Taxonomies.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		let args = {
			name: wrap.find( '.bdb-book-taxonomy-name input' ).val(),
			slug: wrap.find( '.bdb-book-taxonomy-slug input' ).val(),
			format: wrap.find( '.bdb-book-taxonomy-format select' ).val()
		};

		BDB_Book_Taxonomies.checkRequiredFields( args ).then( function( requirementsResponse ) {
			return apiRequest( 'v1/taxonomy/update/' + wrap.data( 'id' ), args, 'POST' )
		} ).then( function( apiResponse ) {
			unspinButton( button );
		} ).catch( function( errorMessage ) {
			BDB_Book_Taxonomies.errorWrap.append( errorMessage ).show();
			unspinButton( button );
		} );

	},

	/**
	 * Delete a taxonomy
	 *
	 * @param e
	 * @returns {boolean}
	 */
	deleteTaxonomy: function ( e ) {

		e.preventDefault();

		if ( ! confirm( bdbVars.confirm_delete_taxonomy ) ) {
			return false;
		}

		let button = jQuery( this );

		spinButton( button );
		BDB_Book_Taxonomies.errorWrap.empty().hide();

		let wrap = button.closest( 'tr' );

		apiRequest( 'v1/taxonomy/delete/' + wrap.data( 'id' ), {}, 'DELETE' ).then( function( apiResponse ) {
			wrap.remove();
		} ).catch( function( errorMessage ) {
			BDB_Book_Taxonomies.errorWrap.append( errorMessage ).show();
			unspinButton( button );
		} );

	},

	/**
	 * Check required fields are filled out
	 *
	 * @param {object} args
	 * @returns {Promise}
	 */
	checkRequiredFields: function( args ) {

		return new Promise( function( resolve, reject ) {

			if ( ! args.hasOwnProperty( 'name' ) || '' === args.name ) {
				reject( bdbVars.error_required_fields );

				return;
			}

			if ( ! args.hasOwnProperty( 'slug' ) || '' === args.slug ) {
				reject( bdbVars.error_required_fields );

				return;
			}

			if ( ! args.hasOwnProperty( 'format' ) || '' === args.format ) {
				reject( bdbVars.error_required_fields );

				return;
			}

			resolve();

		} );

	}

};

export { BDB_Book_Taxonomies };
