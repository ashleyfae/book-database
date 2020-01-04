<?php
/**
 * Dataset: Most Read Genres
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Most_Read_Genres
 *
 * @package Book_Database\Analytics
 */
class Most_Read_Genres extends Dataset {

	protected $type = 'table';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_log    = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_term_r = book_database()->get_table( 'book_term_relationships' )->get_table_name();
		$tbl_terms  = book_database()->get_table( 'book_terms' )->get_table_name();

		$query = "SELECT COUNT( log.id ) AS count, t.name AS name
		FROM {$tbl_log} AS log 
		INNER JOIN {$tbl_term_r} AS tr ON( log.book_id = tr.book_id )
		INNER JOIN {$tbl_terms} AS t ON( tr.term_id = t.id )
		WHERE t.taxonomy = 'genre'
		{$this->get_date_condition( 'log.date_finished', 'log.date_finished' )}
		GROUP BY t.name 
		ORDER BY count DESC 
		LIMIT 5";

		$results = $this->get_db()->get_results( $query );

		return $results;

	}
}