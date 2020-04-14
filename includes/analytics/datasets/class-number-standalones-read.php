<?php
/**
 * Dataset: Number of Standalones Read
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;


use function Book_Database\book_database;

/**
 * Class Number_Standalones_Read
 *
 * @package Book_Database\Analytics
 */
class Number_Standalones_Read extends Dataset {

	/**
	 * Get the number of different series that were read
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_logs  = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		$query = "SELECT COUNT(*)
			FROM {$tbl_logs} AS log 
			INNER JOIN {$tbl_books} AS book ON ( book.id = log.book_id )
			WHERE date_finished IS NOT NULL 
			AND series_id IS NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		//$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		return round( absint( $result ) );

	}

}