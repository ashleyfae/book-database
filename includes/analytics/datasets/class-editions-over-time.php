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

		if ( ! empty( $this->date_start ) && ! empty( $this->date_end ) ) {
			$start = $this->date_start;
			$end   = $this->date_end;
		} else {
			$start = $this->get_db()->get_var(
				"SELECT date_acquired FROM {$tbl_editions}
				WHERE date_acquired IS NOT NULL 
				ORDER BY date_acquired ASC LIMIT 1"
			);

			$end = $this->get_db()->get_var(
				"SELECT date_acquired FROM {$tbl_editions}
				WHERE date_acquired IS NOT NULL 
				ORDER BY date_acquired DESC LIMIT 1"
			);
		}

		$graph->set_range( $start, $end );

		try {
			$graph->set_timestamps();
		} catch ( \Exception $e ) {
			return $graph->get_args();
		}

		// Figure out our group by, based on the date interval.
		if ( 'month' === $graph->get_interval() ) {
			$groupby = "YEAR(date_acquired), MONTH(date_acquired)";
		} else {
			$groupby = "YEAR(date_acquired), MONTH(date_acquired), DAY(date_acquired)";
		}

		$query = "SELECT date_acquired, COUNT(id) AS number_books
			FROM {$tbl_editions}
			WHERE date_acquired IS NOT NULL
			{$this->get_date_condition( 'date_acquired', 'date_acquired' )}
			GROUP BY {$groupby}
			ORDER BY date_acquired ASC;";

		$this->log( $query, __CLASS__ );

		$raw_rows = $this->get_db()->get_results( $query );

		$result_array = array();

		foreach ( $raw_rows as $row ) {
			$result_array[ $row->date_acquired ] = absint( $row->number_books );
		}

		// Now convert back to objects.
		$final_periods = array();
		foreach ( $graph->fill_data( $result_array ) as $date => $value ) {
			$dataset               = new \stdClass();
			$dataset->period       = $date;
			$dataset->number_books = $value;
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