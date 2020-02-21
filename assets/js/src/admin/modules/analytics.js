/* global $, bdbVars, wp */

import { apiRequest, spinButton, unspinButton } from 'utils';
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

		$( '#bdb-analytics-date-range' ).on( 'submit', this.setDateRange );
	},

	/**
	 * Set the date range
	 *
	 * @param e
	 */
	setDateRange: function ( e ) {

		e.preventDefault();

		let args = {
			option: $( '#bdb-analytics-date-range-select' ).val()
		};

		const button = $( '#bdb-analytics-set-date-range' );

		spinButton( button );

		apiRequest( 'v1/analytics/range', args, 'POST' ).then( function( apiResponse ) {
			$( '.bdb-analytics-start-date' ).text( apiResponse.start );
			$( '.bdb-analytics-end-date' ).text( apiResponse.end );
			$( '.bdb-analytics-start-date-formatted' ).text( apiResponse.start_formatted );
			$( '.bdb-analytics-end-date-formatted' ).text( apiResponse.end_formatted );

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

		$( '.bdb-analytics-block' ).each( function( blockIndex, block ) {

			const blockWrap = $( this );
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

			$.each( blockWrap.data(), function( argKey, argValue ) {
				if ( 0 === argKey.indexOf( 'arg_' ) ) {
					args[ argKey.replace( 'arg_', '' ) ] = argValue;
				}
			} );

			apiRequest( 'v1/analytics/dataset/' + dataset, args, 'GET' ).then( function( apiResponse ) {
				console.log( apiResponse.type, apiResponse );
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
				console.log( 'Chart Data', apiResponse.data.chart.data );
				console.log( 'Chart Type', type );

				am4core.createFromConfig( apiResponse.data.chart, id, type );
				break;

			case 'table' :
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
					$.each( apiResponse.data, function( datasetID, dataValue ) {
						blockWrap.find( '#bdb-dataset-' + datasetID + ' .bdb-dataset-value' ).empty().append( dataValue );
					} );
				}
				break;

			default :
				// Simple text value
				blockWrap.find( '.bdb-dataset-value' ).empty().append( apiResponse.data );
				break;

		}

	},

	shapeConfig: function ( config ) {

		console.log( config );

		return config;

		const letters = '0123456789ABCDEF'.split('');

		if ( 'pie' === config.type ) {
			for ( let dataSet = 0; dataSet < config.data.datasets.length; dataSet++ ) {
				config.data.datasets[dataSet].backgroundColor = [];
				config.data.datasets[dataSet].borderColor = '#ffffff';

				for ( let labels = 0; labels < config.data.labels.length; labels++ ) {

					let colour = '#';

					for ( let i = 0; i < 6; i++ ) {
						colour += letters[Math.floor( Math.random() * 16 )];
					}

					console.log( 'Colour', colour );

					config.data.datasets[dataSet].backgroundColor.push( colour );
				}
			}
		}

		console.log( config );

		return config;

	}

};

export { BDB_Analytics }