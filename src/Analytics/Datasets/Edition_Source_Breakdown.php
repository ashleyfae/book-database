<?php
/**
 * Dataset: Edition Source Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use Book_Database\Analytics\Pie_Chart;
use function Book_Database\book_database;

/**
 * Class Edition_Source_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Edition_Source_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart( array(
			'series' => array(
				array(
					'type'       => 'PieSeries',
					'name'       => __( 'Edition Source Breakdown', 'book-database' ),
					'dataFields' => array(
						'category' => 'source',
						'value'    => 'number_editions'
					),
					'slices'     => array(
						'tooltipText' => '{category}: {value.value} Books',
					),
				)
			),
			'legend' => array(
				'type' => 'Legend',
			),
		) );

		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();
		$tbl_terms    = book_database()->get_table( 'book_terms' )->get_table_name();

		$query = "SELECT COUNT( DISTINCT edition.id ) AS number_editions, t.name AS source
		FROM {$tbl_editions} AS edition
		INNER JOIN {$tbl_terms} AS t ON( edition.source_id = t.id )
		WHERE edition.date_acquired IS NOT NULL 
		{$this->get_date_condition( 'date_acquired', 'date_acquired' )}
		GROUP BY edition.source_id";

		$this->log( $query, __CLASS__ );

		$results = $this->get_db()->get_results( $query );

		$chart->add_dataset( $results );

		return $chart->get_args();

	}
}
