/* global $, bdbVars, wp */

/**
 * Category style checkboxes
 */
var BDB_Categories = {

	/**
	 * Initialize
	 */
	init: function() {

		$( '.bdb-new-checkbox-term' ).on( 'click', '.button', this.addCheckboxTerm );
		$( '.bdb-new-checkbox-term-value' ).on( 'keypress', this.addCheckboxTerm );

	},

	addCheckboxTerm: function ( e ) {

		if ( 'click' === e.type ) {
			e.preventDefault();
		}

		if ( 'keypress' === e.type && 13 !== e.which ) {
			return true;
		} else {
			e.preventDefault();
		}

		let wrap = $( this ).closest( '.bdb-taxonomy-checkboxes' ),
			checkboxName = wrap.data( 'name' ),
			checkboxTaxonomy = wrap.data( 'taxonomy' ),
			checkboxWrap = wrap.find( '.bdb-checkbox-wrap' ),
			newTerm = wrap.find( '.bdb-new-checkbox-term-value' ),
			elID = BDB_Categories.createID( newTerm.val(), checkboxTaxonomy + '-' );

		checkboxWrap.append( '<label for="' + elID + '"><input type="checkbox" id="' + elID + '" name="' + checkboxName + '" class="bdb" value="' + newTerm.val() + '" checked="checked"> ' + newTerm.val() + '</label>' );

		newTerm.val( '' );

	},

	createID: function ( value, prefix ) {

		return value.replace( /[^a-z0-9]/g, function( s ) {
			let c = s.charCodeAt( 0 );

			if ( 32 === c ) {
				return '-';
			}

			if ( c >= 65 && c <= 90 ) {
				return prefix + s.toLowerCase();
			}

			return prefix + ( '000' + c.toString( 16 ) ).slice( -4 );
		} );

	}

};

export { BDB_Categories }