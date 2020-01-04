<?php
/**
 * Pie Chart
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Class Pie_Chart
 *
 * @package Book_Database\Analytics
 */
class Pie_Chart extends Graph {

	protected $type = 'pie';

	/**
	 * Graph constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		parent::__construct( wp_parse_args( $args, array(
			'type' => $this->type,
			'data' => array(
				'labels'   => array(),
				'datasets' => array(),
				'options'  => array(
					'responsive'          => true,
					'maintainAspectRatio' => false,
				)
			)
		) ) );

	}

}