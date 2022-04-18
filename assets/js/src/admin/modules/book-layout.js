/* global $, bdbVars, wp */

/**
 * Book Layout
 */
var BDB_Book_Layout = {

	/**
	 * Initialize
	 */
	init: function() {
		jQuery( '.bdb-book-option-toggle' ).on( 'click', this.toggleBookTextarea );
		jQuery( '#bdb-book-layout-cover-changer' ).on( 'change', this.changeCoverAlignment );

		this.sort();
	},

	/**
	 * Toggle the book textarea
	 *
	 * @param e
	 */
	toggleBookTextarea: function( e ) {
		jQuery( this ).next().slideToggle();
	},

	/**
	 * Change the real-time alignment of the book coversss123
	 *
	 * @param e
	 */
	changeCoverAlignment: function ( e ) {
		let parentDiv = jQuery( '#bdb-book-option-cover' );

		parentDiv.removeClass( function ( index, css ) {
			return ( css.match(/(^|\s)bdb-book-cover-align-\S+/g) || [] ).join(' ');
		} ).addClass( 'bdb-book-cover-align-' + jQuery( this ).val() );
	},

	sort: function() {
		jQuery( '.bdb-sortable' ).sortable( {
			cancel: '.bdb-no-sort, textarea, input, select',
			connectWith: '.bdb-sortable',
			placeholder: 'bdb-sortable-placeholder',
			update: function ( event, ui ) {
				let parentID = ui.item.parent().attr( 'id' );
				let disabledIndicator = ui.item.find( '.bdb-book-option-disabled' );
				if ( jQuery( '#' + parentID ).hasClass( 'bdb-sorter-enabled-column' ) ) {
					disabledIndicator.val( 'false' );
				} else {
					disabledIndicator.val( 'true' );
				}
			}
		} )
	}

};

export { BDB_Book_Layout };
