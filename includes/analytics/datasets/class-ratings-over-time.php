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

		$graph = new Graph();

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT date_finished, rating
		FROM {$tbl_log}
		WHERE rating IS NOT NULL 
		AND date_finished IS NOT NULL 
		{$this->get_date_condition( 'date_finished', 'date_finished' )}
		ORDER BY date_finished ASC
		";

		$results           = $this->get_db()->get_results( $query );
		$formatted_results = array();

		if ( ! empty( $this->date_start ) && ! empty( $this->date_end ) ) {
			$graph->set_range( $this->date_start, $this->date_end );
		} else {
			$start = $results[0]->date_finished ?? '';
			$end   = $results[ count( $results ) - 1 ]->date_finished ?? '';

			$graph->set_range( $start, $end );
		}

		foreach ( $results as $result ) {
			$formatted_results[ $result->date_finished ] = $result->rating;
		}

		$graph->add_dataset( array(
			'fill'     => false,
			'showLine' => false
		), $formatted_results );

		return $graph->get_args();

	}
}