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
		let dateField = $( '.bdb-datepicker' ),
			config = {
				allowInput: true,
				altInput: true,
				altFormat: 'F J, Y',
				dateFormat: 'Y-m-d'
			};

		if ( dateField.hasClass( 'bdb-timepicker' ) ) {
			config.enableTime = true;
			config.dateFormat = 'Y-m-d H:i';
			config.altFormat = 'F J, Y, h:i K';
		}

		dateField.flatpickr( config );
	}

};

export { BDB_Datepicker }