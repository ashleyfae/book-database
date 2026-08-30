/**
 * amCharts Loader
 *
 * Loaded as a script module so amcharts4's own dynamic imports (used for
 * PDF/XLSX/PNG chart export) stay as separate, lazily-fetched chunks
 * instead of being inlined into book-graphs.js.
 *
 * @package book-database
 * @copyright Copyright (c) 2026, Ashley Gibson
 * @license GPL2+
 */

import * as am4core from '@amcharts/amcharts4/core';
import * as am4charts from '@amcharts/amcharts4/charts';

window.am4core = am4core;
window.am4charts = am4charts;

document.dispatchEvent( new Event( 'bdb-amcharts-ready' ) );
