<?php
/**
 * Dataset: Number of Reviews Written
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Number_Reviews_Written
 *
 * @package Book_Database\Analytics
 */
class Number_Reviews_Written extends Dataset {

	/**
	 * Get the number of reviews that were written
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_reviews = book_database()->get_table( 'reviews' )->get_table_name();

		$query = "SELECT COUNT(*)
			FROM {$tbl_reviews}
			WHERE date_written IS NOT NULL 
			{$this->get_date_condition( 'date_written', 'date_written' )}";

		//$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		return round( absint( $result ) );

	}

}
