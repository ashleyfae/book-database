<?php
/**
 * Dataset: Average number of days to finish a book
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Average_Days_Finish_Book
 *
 * @package Book_Database\Analytics
 */
class Average_Days_Finish_Book extends Dataset {

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT ROUND( AVG( DATEDIFF( date_finished, date_started ) * percentage_complete ) ) + 1 AS number_days
		FROM {$tbl_log}
		WHERE date_started IS NOT NULL 
		AND date_finished IS NOT NULL 
		{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		return $this->get_db()->get_var( $query );

	}
}
