<?php
/**
 * Dataset: Ratings Over Time
 *
 * Scatter plot
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
use function Book_Database\format_date;

/**
 * Class Ratings_Over_Time
 *
 * @package Book_Database\Analytics
 */
class Ratings_Over_Time extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$graph = new Graph( array(
			'options' => array(
				'legend' => array(
					'display' => false
				),
				'yAxes'  => array(
					array(
						'ticks' => array(
							'min'       => 0,
							'precision' => 0
						)
					)
				),
			)
		) );

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		if ( ! empty( $this->date_start ) && ! empty( $this->date_end ) ) {
			$start = $this->date_start;
			$end   = $this->date_end;
		} else {
			$start = $this->get_db()->get_var(
				"SELECT date_finished FROM {$tbl_log}
				WHERE date_finished IS NOT NULL
				ORDER BY date_finished ASC LIMIT 1"
			);

			$end = $this->get_db()->get_var(
				"SELECT date_finished FROM {$tbl_log}
				WHERE date_finished IS NOT NULL
				ORDER BY date_finished DESC LIMIT 1"
			);
		}

		$query = "SELECT DATE_FORMAT( date_finished, '%Y-%m-%d' ) AS date_finished, rating
		FROM {$tbl_log}
		WHERE rating IS NOT NULL 
		AND date_finished IS NOT NULL 
		{$this->get_date_condition( 'date_finished', 'date_finished' )}
		ORDER BY date_finished ASC
		";

		$results           = $this->get_db()->get_results( $query );
		$formatted_results = array();

		try {
			$period = new DatePeriod( new DateTime( $start ), new DateInterval( 'P1D' ), new DateTime( $end ) );

			foreach ( $period as $datetime ) {
				/**
				 * @var DateTime $datetime
				 */
				$formatted_results[ $datetime->format( 'Y-m-d' ) ] = null;
			}
		} catch ( \Exception $e ) {

		}

		foreach ( $results as $result ) {
			$formatted_results[ $result->date_finished ] = $result->rating;
		}

		$graph->add_dataset( array(
			'fill'     => false,
			'showLine' => false,
			'backgroundColor' => 'blue',
			'borderColor' => 'blue',
			'pointStyle' => 'circle'
		), array_values( $formatted_results ), false );

		$graph->set_labels( array_keys( $formatted_results ) );

		return $graph->get_args();

	}
}