<?php
/**
 * Scatter Chart
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Class Scatter_Chart
 *
 * @package Book_Database\Analytics
 */
class Scatter_Chart extends Graph {

	/**
	 * @var string Type of graph.
	 */
	protected $type = 'XYChart';

	/**
	 * @var string Type of series.
	 */
	protected $series_type = 'LineSeries';

}