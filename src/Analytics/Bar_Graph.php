<?php
/**
 * Bar Graph
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Class Bar_Graph
 *
 * @package Book_Database\Analytics
 */
class Bar_Graph extends Graph {

	/**
	 * @var string Type of graph.
	 */
	protected $type = 'XYChart';

	/**
	 * @var string Type of series.
	 */
	protected $series_type = 'ColumnSeries';

}
