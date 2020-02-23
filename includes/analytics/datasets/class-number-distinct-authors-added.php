<?php
/**
 * Dataset: Number of Distinct Authors Added
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Number_Distinct_Authors_Added
 *
 * @package Book_Database\Analytics
 */
class Number_Distinct_Authors_Added extends Dataset {

	/**
	 * return int
	 */
	protected function _get_dataset() {

		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();

		$query = "SELECT DISTINCT author_id
			FROM {$tbl_books} AS book
			INNER JOIN {$tbl_author_r} AS ar ON( book.id = ar.book_id )
			WHERE 1=1
			{$this->get_date_condition( 'date_created', 'date_created' )}";

		return absint( $this->get_db()->get_var( $query ) );

	}

}