<?php
/**
 * Dataset: Editions Acquired Over Time
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
 * Class Editions_Over_Time
 *
 * @package Book_Database\Analytics
 */
class Editions_Over_Time extends Dataset {

	protected $type = 'graph';

	/**
	 * Graph editions acquired over time
	 *
	 * @return array Array of Chart settings.
	 */
	protected function _get_dataset() {

		$graph = new Bar_Graph( array(
			'options' => array(
				'chartArea' => array(
					'width'  => '95%',
					'height' => '80%'
				),
				'legend'    => 'none',
				'hAxis'     => array(
					'title'  => __( 'Date Acquired', 'book-database' ),
					'format' => 'YYYY-MM'
				)
			)
		) );

		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$periods  = $final_periods = array();
		$raw_rows = $this->get_db()->get_results(
			"SELECT DATE_FORMAT( date_acquired, '%Y-%m' ) AS period, COUNT(id) AS number_books
			FROM {$tbl_editions}
			WHERE date_acquired IS NOT NULL
			GROUP BY period
			ORDER BY period ASC;"
		);

		if ( empty( $raw_rows ) ) {
			return $graph->get_args();
		}

		$first_period = $raw_rows[0]->period ?? false;
		$last_period  = ( new DateTime() )->modify( '+1 month' )->format( 'Y-m' ); // Add +1 because DatePeriod doesn't include the end date.

		// If possible, fill up our array with default values.
		if ( ! empty( $first_period ) && ! empty( $last_period ) ) {
			try {
				$period = new DatePeriod( new DateTime( sprintf( '%s-01', $first_period ) ), new DateInterval( 'P1M' ), new DateTime( sprintf( '%s-01', $last_period ) ) );

				foreach ( $period as $datetime ) {
					/**
					 * @var \DateTime $datetime
					 */
					$periods[ $datetime->format( 'Y-m' ) ] = 0;
				}
			} catch ( \Exception $e ) {

			}
		}

		// Now fill up with our legit values.
		foreach ( $raw_rows as $raw_row ) {
			$periods[ $raw_row->period ] = absint( $raw_row->number_books );
		}

		// Now convert back to objects.
		foreach ( $periods as $date => $value ) {
			$dataset               = new \stdClass();
			$dataset->period       = $date;
			$dataset->number_books = absint( $value );
			$final_periods[]       = $dataset;
		}

		$columns = array(
			array(
				'id'    => 'period',
				'label' => esc_html__( 'Date', 'book-database' ),
				'type'  => 'string'
			),
			array(
				'id'      => 'number_books',
				'label'   => esc_html__( 'Editions Added', 'book-database' ),
				'type'    => 'number',
				'display' => esc_html__( '%d Editions', 'book-database' )
			)
		);

		$graph->add_dataset( $columns, $final_periods );

		return $graph->get_args();

	}

}