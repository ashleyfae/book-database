<?php
/**
 * Dataset: Books Read Over Time
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Books_Read_Over_Time
 *
 * @package Book_Database\Analytics
 */
class Books_Read_Over_Time extends Dataset {

	protected $type = 'graph';

	/**
	 * Graph books read over time
	 *
	 * @return array Array of Chart settings.
	 */
	protected function _get_dataset() {

		$graph = new Bar_Graph( array(
			'series' => array(
				array(
					'type'       => 'ColumnSeries',
					'name'       => __( 'Books Read Over Time', 'book-database' ),
					'dataFields' => array(
						'categoryX' => 'date',
						'valueY'    => 'number_books'
					),
					'columns'    => array(
						'tooltipText' => __( 'Books Read in {categoryX}: {valueY}', 'book-database' ),
					)
				)
			),
			'yAxes'  => array(
				array(
					'type'         => 'ValueAxis',
					'title'        => array(
						'text' => __( 'Number of Books Read', 'book-database' )
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
			)
		) );

		$tbl_log   = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

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

		$this->log( sprintf( 'Start: %s', $start ), __CLASS__ );
		$this->log( sprintf( 'End: %s', $end ), __CLASS__ );

		$graph->set_range( $start, $end );

		try {
			$graph->set_timestamps();
		} catch ( \Exception $e ) {
			return $graph->get_args();
		}

		// Figure out our group by, based on the date interval.
		if ( 'month' === $graph->get_interval() ) {
			$groupby     = "YEAR(date_finished), MONTH(date_finished)";
			$date_format = "%Y-%m";
		} else {
			$groupby     = "YEAR(date_finished), MONTH(date_finished), DAY(date_finished)";
			$date_format = "%Y-%m-%d";
		}

		$query = "SELECT DATE_FORMAT( date_finished, '{$date_format}' ) AS date_finished, COUNT(log.id) AS number_books, GROUP_CONCAT(book.title SEPARATOR '\n') AS book_titles
			FROM {$tbl_log} AS log
			INNER JOIN {$tbl_books} AS book ON(log.book_id = book.id) 
			WHERE date_finished IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )}
			GROUP BY {$groupby}
			ORDER BY date_finished ASC;";

		$this->log( $query, __CLASS__ );

		$raw_rows = $this->get_db()->get_results( $query );

		$result_array = array();

		foreach ( $raw_rows as $row ) {
			$result_array[ $row->date_finished ] = absint( $row->number_books );
		}

		// Now convert back to objects.
		$final_periods = array();
		foreach ( $graph->fill_data( $result_array ) as $date => $value ) {
			$dataset               = new \stdClass();
			$dataset->date         = $date;
			$dataset->number_books = $value;
			$final_periods[]       = $dataset;
		}

		$graph->add_dataset( $final_periods );

		return $graph->get_args();

	}

}