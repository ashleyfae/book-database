<?php
/**
 * Number of Pages Read
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Pages_Read
 *
 * @package Book_Database\Analytics
 */
class Pages_Read extends Dataset {

	/**
	 * Get the number of pages read
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_books = book_database()->get_table( 'books' )->get_table_name();
		$tbl_logs  = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT SUM(pages * percentage_complete) as pages_read
				FROM {$tbl_books} AS book
				INNER JOIN {$tbl_logs} AS log ON( log.book_id = book.id )
				WHERE date_finished IS NOT NULL
				{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		//$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		return round( absint( $result ) );

	}
}
