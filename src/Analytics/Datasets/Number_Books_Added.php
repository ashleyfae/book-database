<?php
/**
 * Dataset: Number of Books Added
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Number_Books_Added
 *
 * @package Book_Database\Analytics
 */
class Number_Books_Added extends Dataset {

	/**
	 * Get the number of books that were added
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		$query = "SELECT COUNT(*)
			FROM {$tbl_books}
			WHERE 1=1
			{$this->get_date_condition( 'date_created', 'date_created' )}";

		return absint( $this->get_db()->get_var( $query ) );

	}

}
