/* global $, bdbVars, wp */

/**
 * Datepicker
 */
var BDB_Datepicker = {

	/**
	 * Initialize
	 */
	init: function() {

		if ( $( '.bdb-datepicker' ).length > 0 ) {
			this.setDatepickers();
		}

		$( document ).on( 'bdb_editions_loaded', this.setDatepickers );
		$( document ).on( 'bdb_edition_added', this.setDatepickers );
		$( document ).on( 'bdb_edition_updated', this.setDatepickers );

		$( document ).on( 'bdb_reading_logs_loaded', this.setDatepickers );
		$( document ).on( 'bdb_reading_log_added', this.setDatepickers );
		$( document ).on( 'bdb_reading_log_updated', this.setDatepickers );

	},

	/**
	 * Set datepickers
	 */
	setDatepickers: function() {
		$( '.bdb-datepicker' ).datepicker( {
			dateFormat: 'yy-mm-dd',
			beforeShow: function() {
				$( this ).datepicker( 'widget' ).addClass( 'bdb-datepicker-wrap' );
			},
			onClose: function() {
				$( this ).datepicker( 'widget' ).removeClass( 'bdb-datepicker-wrap' );
			}
		} );
	}

};

export { BDB_Datepicker }