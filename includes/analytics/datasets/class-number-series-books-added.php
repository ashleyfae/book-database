<?php
/**
 * Dataset: Number of Series Books Added
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Number_Series_Books_Added
 *
 * @package Book_Database\Analytics
 */
class Number_Series_Books_Added extends Dataset {

	/**
	 * Get the number of series books that were added
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		$query = "SELECT COUNT(*)
			FROM {$tbl_books}
			WHERE series_id IS NOT NULL
			{$this->get_date_condition( 'date_created', 'date_created' )}";

		return absint( $this->get_db()->get_var( $query ) );

	}

}