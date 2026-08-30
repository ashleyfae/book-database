/**
 * Resolve once `window.am4core` is available.
 *
 * amcharts4 is loaded separately as a script module (see amcharts-loader.js),
 * which is always deferred relative to classic scripts. This normally
 * resolves immediately since it's only ever awaited from an async API
 * response callback, well after the module has had time to execute, but we
 * guard against the module not having run yet (or failing to load at all)
 * rather than assuming it's ready.
 *
 * @param {number} timeout Milliseconds to wait before giving up. Default 10000.
 * @return {Promise<object>} Resolves with `window.am4core`, rejects if it times out.
 */
export function waitForAmCharts( timeout = 10000 ) {

	if ( 'undefined' !== typeof window.am4core ) {
		return Promise.resolve( window.am4core );
	}

	return new Promise( function ( resolve, reject ) {

		const timer = setTimeout( function () {
			document.removeEventListener( 'bdb-amcharts-ready', onReady );
			reject( new Error( 'amCharts failed to load in time.' ) );
		}, timeout );

		function onReady() {
			clearTimeout( timer );
			resolve( window.am4core );
		}

		document.addEventListener( 'bdb-amcharts-ready', onReady, { once: true } );

	} );

}
