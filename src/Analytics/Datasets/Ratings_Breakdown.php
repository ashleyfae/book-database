<?php
/**
 * Dataset: Ratings Breakdown
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
 * Class Ratings_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Ratings_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart( array(
			'series' => array(
				array(
					'type'       => 'PieSeries',
					'name'       => __( 'Rating Breakdown', 'book-database' ),
					'dataFields' => array(
						'category' => 'rating',
						'value'    => 'number_books'
					),
					'slices'     => array(
						'tooltipText' => '{category}: {value.value}',
					),
				)
			),
			'legend' => array(
				'type'        => 'Legend',
			),
		) );

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT rating, COUNT( IFNULL( rating, 1 ) ) AS number_books
			FROM {$tbl_log} AS log 
			WHERE date_finished IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )} 
			GROUP BY rating
			ORDER BY rating DESC";

		$results   = $this->get_db()->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $index => $result ) {
				$result->rating = is_null( $result->rating ) ? __( 'None', 'book-database' ) : sprintf( __( '%s Stars', 'book-database' ), $result->rating * 1 );
				$result->number_books  = absint( $result->number_books );

				$results[ $index ] = $result;
			}
		}

		$chart->add_dataset( $results );

		return $chart->get_args();

	}
}
