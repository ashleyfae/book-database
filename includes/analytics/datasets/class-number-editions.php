<?php
/**
 * Dataset: Number Editions Added
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Number_Editions
 *
 * @package Book_Database\Analytics
 */
class Number_Editions extends Dataset {

	/**
	 * Get the number of editions that were added
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$query = "SELECT COUNT(*)
			FROM {$tbl_editions}
			WHERE date_acquired IS NOT NULL
			{$this->get_date_condition( 'date_acquired', 'date_acquired' )}";

		//$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		return absint( $result );

	}

}