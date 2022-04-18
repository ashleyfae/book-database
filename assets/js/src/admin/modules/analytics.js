/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from '../../utils';
import * as am4core from "@amcharts/amcharts4/core";
import * as am4charts from "@amcharts/amcharts4/charts";

/**
 * Analytics
 */
var BDB_Analytics = {

	spinner: '<div id="bdb-circleG"><div id="bdb-circleG_1" class="bdb-circleG"></div><div id="bdb-circleG_2" class="bdb-circleG"></div><div id="bdb-circleG_3" class="bdb-circleG"></div></div>',

	/**
	 * Initialize
	 */
	init: function() {
		if ( ! document.getElementById( 'bdb-book-analytics-wrap') ) {
			return;
		}

		this.getBlockValues();

		jQuery( '#bdb-analytics-date-range-select' ).on( 'change', this.maybeToggleCustomFields );
		jQuery( '#bdb-analytics-date-range' ).on( 'submit', this.setDateRange );
	},

	/**
	 * Show the start and end fields if the range is set to "custom".
	 *
	 * @param e
	 */
	maybeToggleCustomFields: function ( e ) {

		const range = jQuery( this ).val(),
			start = jQuery( '#bdb-analytics-start-date' ),
			end = jQuery( '#bdb-analytics-end-date' );

		if ( 'custom' === range ) {
			start.show();
			end.show();
		} else {
			start.hide().val( '' );
			end.hide().val( '' );
		}

	},

	/**
	 * Set the date range
	 *
	 * @param e
	 */
	setDateRange: function ( e ) {

		e.preventDefault();

		let args = {
			option: jQuery( '#bdb-analytics-date-range-select' ).val()
		};

		if ( 'custom' === args.option ) {
			args.start = jQuery( '#bdb-analytics-start-date' ).val();
			args.end = jQuery( '#bdb-analytics-end-date' ).val();
		}

		const button = jQuery( '#bdb-analytics-set-date-range' );

		spinButton( button );

		apiRequest( 'v1/analytics/range', args, 'POST' ).then( function( apiResponse ) {
			jQuery( '.bdb-analytics-start-date' ).text( apiResponse.start );
			jQuery( '.bdb-analytics-end-date' ).text( apiResponse.end );
			jQuery( '.bdb-analytics-start-date-formatted' ).text( apiResponse.start_formatted );
			jQuery( '.bdb-analytics-end-date-formatted' ).text( apiResponse.end_formatted );

			return BDB_Analytics.getBlockValues();
		} ).catch( function( error ) {
			console.log( 'Set date range error', error );
		} ).finally( function() {
			unspinButton( button );
		} );

	},

	/**
	 * Get values of all the blocks
	 */
	getBlockValues: function() {

		jQuery( '.bdb-analytics-block' ).each( function( blockIndex, block ) {

			const blockWrap = jQuery( this );
			const dataset = blockWrap.data( 'dataset' );

			// Spinners
			if ( blockWrap.hasClass( 'bdb-dataset-type-value' ) || blockWrap.hasClass( 'bdb-dataset-type-dataset' ) ) {
				blockWrap.find( '.bdb-dataset-value' ).empty().append( BDB_Analytics.spinner );
			}

			let args = {};

			if ( 'undefined' !== typeof blockWrap.data( 'period' ) ) {
				args.date_option = blockWrap.data( 'period' );
			}

			if ( 'undefined' === typeof dataset ) {
				return;
			}

			jQuery.each( blockWrap.data(), function( argKey, argValue ) {
				if ( 0 === argKey.indexOf( 'arg_' ) ) {
					args[ argKey.replace( 'arg_', '' ) ] = argValue;
				}
			} );

			apiRequest( 'v1/analytics/dataset/' + dataset, args, 'GET' ).then( function( apiResponse ) {
				BDB_Analytics.renderDataset( blockWrap, apiResponse );
			} ).catch( function( error ) {
				console.log( 'Render error', error );

				blockWrap.find( '.bdb-dataset-value' ).empty().append( error );
			} );

		} );

	},

	/**
	 * Render an individual dataset
	 *
	 * @param {object} blockWrap
	 * @param {object} apiResponse
	 */
	renderDataset: function ( blockWrap, apiResponse ) {

		if ( 'undefined' === typeof apiResponse.data ) {
			return;
		}

		switch( apiResponse.type ) {

			case 'graph' :
				if ( 'undefined' === typeof apiResponse.type ) {
					console.log( ' undefined type' );

					return;
				}

				const id = blockWrap.data( 'canvas' );

				if ( 'undefined' === typeof id || '' === id ) {
					console.log( 'undefined ID' );

					return;
				}

				const type = apiResponse.data.type;

				am4core.createFromConfig( apiResponse.data.chart, id, type );
				break;

			case 'template' :
				let templateID = '';

				if ( apiResponse.data.length > 0 ) {
					templateID = blockWrap.find( '.bdb-analytics-template' ).attr( 'id' );
				} else {
					templateID = blockWrap.find( '.bdb-analytics-template-none' ).attr( 'id' );
				}

				if ( 'undefined' === typeof templateID || '' === templateID ) {
					return;
				}

				// Strip tmpl-
				templateID = templateID.replace( 'tmpl-', '' );

				const template = wp.template( templateID );

				blockWrap.find( '.bdb-dataset-value' ).empty().append( template( apiResponse.data ) );
				break;

			case 'dataset' :
				// Multiple values
				if ( 0 === apiResponse.data.length ) {
					blockWrap.find( '.bdb-dataset-value' ).empty().append( '&ndash;' );
				} else {
					jQuery.each( apiResponse.data, function( datasetID, dataValue ) {
						blockWrap.find( '#bdb-dataset-' + datasetID + ' .bdb-dataset-value' ).empty().append( dataValue );
					} );
				}
				break;

			default :
				// Simple text value
				blockWrap.find( '.bdb-dataset-value' ).empty().append( apiResponse.data );
				break;

		}

	}

};

export { BDB_Analytics }
