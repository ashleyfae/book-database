<?php
/**
 * Dataset: Reviews Written Over Time
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Reviews_Over_Time
 *
 * @package Book_Database\Analytics
 */
class Reviews_Over_Time extends Dataset {

	protected $type = 'graph';

	/**
	 * Graph reviews written over time
	 *
	 * @return array Array of Chart settings.
	 */
	protected function _get_dataset() {

		$graph = new Bar_Graph( array(
			'series' => array(
				array(
					'type'       => 'ColumnSeries',
					'name'       => __( 'Reviews Written Over Time', 'book-database' ),
					'dataFields' => array(
						'categoryX' => 'date',
						'valueY'    => 'number_reviews'
					),
					'columns'    => array(
						'tooltipText' => __( 'Reviews Written in {categoryX}: {valueY}', 'book-database' ),
					)
				)
			),
			'yAxes'  => array(
				array(
					'type'         => 'ValueAxis',
					'title'        => array(
						'text' => __( 'Number of Reviews Written', 'book-database' )
					),
					'maxPrecision' => 0,
					'min'          => 0
				)
			),
			'xAxes'  => array(
				array(
					'type'       => 'CategoryAxis',
					'title'      => array(
						'text' => __( 'Date', 'book-database' )
					),
					'dataFields' => array(
						'category' => 'date'
					),
				)
			),
			'cursor' => array(
				'type' => 'XYCursor'
			)
		) );

		$tbl_reviews = book_database()->get_table( 'reviews' )->get_table_name();

		if ( ! empty( $this->date_start ) && ! empty( $this->date_end ) ) {
			$start = $this->date_start;
			$end   = $this->date_end;
		} else {
			$start = $this->get_db()->get_var(
				"SELECT date_written FROM {$tbl_reviews}
				WHERE date_written IS NOT NULL 
				ORDER BY date_written ASC LIMIT 1"
			);

			$end = $this->get_db()->get_var(
				"SELECT date_written FROM {$tbl_reviews}
				WHERE date_written IS NOT NULL 
				ORDER BY date_written DESC LIMIT 1"
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
			$groupby = "YEAR(date_written), MONTH(date_written)";
		} else {
			$groupby = "YEAR(date_written), MONTH(date_written), DAY(date_written)";
		}

		$query = "SELECT date_written, COUNT(id) AS number_reviews
			FROM {$tbl_reviews}
			WHERE date_written IS NOT NULL
			{$this->get_date_condition( 'date_written', 'date_written' )}
			GROUP BY {$groupby}
			ORDER BY date_written ASC;";

		$this->log( $query, __CLASS__ );

		$raw_rows = $this->get_db()->get_results( $query );

		$result_array = array();

		foreach ( $raw_rows as $row ) {
			$result_array[ $row->date_written ] = absint( $row->number_reviews );
		}

		// Now convert back to objects.
		$final_periods = array();
		foreach ( $graph->fill_data( $result_array ) as $date => $value ) {
			$dataset                 = new \stdClass();
			$dataset->date           = $date;
			$dataset->number_reviews = $value;
			$final_periods[]         = $dataset;
		}

		$graph->add_dataset( $final_periods );

		return $graph->get_args();

	}

}