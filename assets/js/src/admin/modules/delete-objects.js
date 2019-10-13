/* global $, bdbVars, wp */

/**
 * Confirmation when deleting objects
 */
var BDB_Delete_Objects = {

	/**
	 * Initialize
	 */
	init: function() {
		$( '.bdb-delete-item' ).on( 'click', this.confirm );
	},

	/**
	 * Confirm deleting the item
	 *
	 * @param e
	 * @returns {boolean}
	 */
	confirm: function( e ) {
		let type = $( this ).data( 'object' );
		let message = bdbVars['confirm_delete_' + type];

		if ( ! confirm( message ) ) {
			return false;
		}

		return true;
	}

};

export { BDB_Delete_Objects }