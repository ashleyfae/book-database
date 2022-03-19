<?php
/**
 * Dataset: Reading Track
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Reading_Track
 *
 * @package Book_Database\Analytics
 */
class Reading_Track extends Dataset {

	/**
	 * Return the number of books on track to be read this year
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		/**
		 * Number of books finished
		 */
		$query = "SELECT COUNT(*) FROM {$tbl_log}
				WHERE date_finished IS NOT NULL 
				AND percentage_complete >= 1
				{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		$books_read = $this->get_db()->get_var( $query );

		try {
			// If end date is in the past, return books read.
			if ( time() > strtotime( $this->date_end ) ) {
				return $books_read;
			}

			$now        = new \DateTime();
			$start_date = new \DateTime( $this->date_start );
			$end_date   = new \DateTime( $this->date_end );

			// Calculate books read per day so far.
			$days_in_period = $now->diff( $start_date )->days;
			$books_per_day  = ( $days_in_period > 0 ) ? $books_read / $days_in_period : 0;

			// Based on books per day, calculate how many we'll read in the remaining days.
			$remaining_days = $end_date->diff( $now )->days;
			$left_to_read   = $books_per_day * $remaining_days;

			return round( $left_to_read + $books_read );
		} catch ( \Exception $e ) {
			return null;
		}

	}
}
