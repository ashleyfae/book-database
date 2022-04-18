<?php
/**
 * Dataset: Number of Different Authors
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Number_Different_Authors_Read
 *
 * @package Book_Database\Analytics
 */
class Number_Different_Authors_Read extends Dataset {

	/**
	 * Get the number of different authors that were read
	 *
	 * @return int
	 */
	protected function _get_dataset() {

		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();
		$tbl_logs     = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT COUNT( DISTINCT ar.author_id )
			FROM {$tbl_author_r} AS ar 
			INNER JOIN {$tbl_logs} AS log ON ( log.book_id = ar.book_id )
			WHERE date_finished IS NOT NULL 
			{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		//$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		return round( absint( $result ) );

	}

}
