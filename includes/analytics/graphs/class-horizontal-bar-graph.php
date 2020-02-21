<?php
/**
 * Horizontal Bar Graph
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Class Horizontal_Bar_Graph
 *
 * @package Book_Database\Analytics
 */
class Horizontal_Bar_Graph extends Graph {

	/**
	 * @var string Type of graph.
	 */
	protected $type = 'XYChart';

	/**
	 * @var string Type of series.
	 */
	protected $series_type = 'ColumnSeries';

}