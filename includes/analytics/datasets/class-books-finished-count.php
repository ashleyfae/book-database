<?php
/**
 * Number of books finished
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Books_Finished_Count
 *
 * @package Book_Database\Analytics
 */
class Books_Finished_Count extends Dataset {

	/**
	 * Get the number of books finished
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT COUNT(*) FROM {$tbl_log}
				WHERE date_finished IS NOT NULL 
				AND percentage_complete >= 1
				{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		return round( absint( $result ) );

	}
}