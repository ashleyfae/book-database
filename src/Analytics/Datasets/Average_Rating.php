<?php
/**
 * class-average-rating.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Average_Rating
 *
 * @package Book_Database\Analytics
 */
class Average_Rating extends Dataset {

	/**
	 * Get the average rating
	 *
	 * @return int|string
	 */
	protected function _get_dataset() {

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT ROUND( AVG( rating ), 2 )
			FROM {$tbl_log}
			WHERE rating IS NOT NULL
			AND date_finished IS NOT NULL 
			{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		//$this->log( $query, __METHOD__ );

		$result = $this->get_db()->get_var( $query );

		if ( is_null( $result ) ) {
			return '&ndash;';
		}

		return floatval( $result ) * 1;

	}

}
