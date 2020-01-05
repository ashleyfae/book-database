<?php
/**
 * class-books-per-year.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use DateInterval;
use DatePeriod;
use DateTime;
use function Book_Database\book_database;

/**
 * Class Books_Per_Year
 *
 * @package Book_Database\Analytics
 */
class Books_Per_Year extends Dataset {

	protected $type = 'graph';

	/**
	 * Calculate books per year
	 *
	 * @return array Array of Chart settings.
	 */
	protected function _get_dataset() {

		$graph = new Horizontal_Bar_Graph( array(
			'data'    => array(
				'labels'   => array(),
				'datasets' => array(),
			),
			'options' => array(
				'title'      => array(
					'text' => ''
				),
				'responsive' => true,
				//'maintainAspectRatio' => false,
				'legend'     => array(
					'display' => false
				),
				'scales'     => array(
					'xAxes' => array(
						array(
							'ticks' => array(
								'min'       => 0,
								'precision' => 0,
							)
						)
					),
					'yAxes' => array(
						array(
							'ticks' => array(
								'autoSkip'    => false,
								'beginAtZero' => true,
								'min'         => 0,
								'precision'   => 0,
								'stepSize'    => 0
							)
						)
					)
				)
			)
		) );

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$years    = $final_years = array();
		$raw_rows = $this->get_db()->get_results(
			"SELECT YEAR( date_finished ) AS year, COUNT(*) AS number_books
			FROM {$tbl_log}
			WHERE date_finished IS NOT NULL
			GROUP BY year
			ORDER BY year DESC;"
		);

		if ( empty( $raw_rows ) ) {
			return $graph->get_args();
		}

		$first_year = $raw_rows[ count( $raw_rows ) - 1 ]->year ?? false;
		$last_year  = date( 'Y' ) + 1; // We add +1 because DatePeriod doesn't include the end date.

		// If possible, fill up our array with default values.
		if ( ! empty( $first_year ) && ! empty( $last_year ) ) {
			try {
				$period = new DatePeriod( new DateTime( sprintf( '%d-01-01', $first_year ) ), new DateInterval( 'P1Y' ), new DateTime( sprintf( '%d-01-01', $last_year ) ) );

				foreach ( $period as $datetime ) {
					/**
					 * @var DateTime $datetime
					 */
					$years[ $datetime->format( 'Y' ) ] = 0;
				}
			} catch ( \Exception $e ) {

			}
		}

		// Now fill up with our legit values.
		foreach ( $raw_rows as $row ) {
			$years[ absint( $row->year ) ] = absint( $row->number_books );
		}

		krsort( $years, SORT_NUMERIC );

		// Now convert back to objects.
		foreach ( $years as $year => $value ) {
			$dataset               = new \stdClass();
			$dataset->year         = absint( $year );
			$dataset->number_books = absint( $value );
			$final_years[]         = $dataset;
		}

		$columns = array(
			array(
				'id'    => 'year',
				'label' => esc_html__( 'Year', 'book-database' ),
				'type'  => 'string'
			),
			array(
				'id'      => 'number_books',
				'label'   => esc_html__( 'Books Read', 'book-database' ),
				'type'    => 'number',
				'display' => esc_html__( '%d Books', 'book-database' )
			)
		);

		$graph->add_dataset( $columns, $final_years );

		return $graph->get_args();

	}
}