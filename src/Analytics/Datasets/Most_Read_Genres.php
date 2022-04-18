<?php
/**
 * Dataset: Most Read Genres
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use function Book_Database\book_database;

/**
 * Class Most_Read_Genres
 *
 * @package Book_Database\Analytics
 */
class Most_Read_Genres extends Dataset {

	protected $type = 'template';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_log    = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_term_r = book_database()->get_table( 'book_term_relationships' )->get_table_name();
		$tbl_terms  = book_database()->get_table( 'book_terms' )->get_table_name();

		$query = "SELECT COUNT( DISTINCT log.book_id ) AS count, t.name AS name
		FROM {$tbl_log} AS log 
		INNER JOIN {$tbl_term_r} AS tr ON( log.book_id = tr.book_id )
		INNER JOIN {$tbl_terms} AS t ON( tr.term_id = t.id )
		WHERE t.taxonomy = 'genre'
		AND log.date_finished IS NOT NULL
		{$this->get_date_condition( 'log.date_finished', 'log.date_finished' )}
		GROUP BY t.name 
		ORDER BY count DESC 
		LIMIT 4";

		//error_log( $query );

		$results = $this->get_db()->get_results( $query );

		// Now query for "other".
		$genres             = wp_list_pluck( $results, 'name' );
		$genre_placeholders = implode( ', ', array_fill( 0, count( $genres ), '%s' ) );

		$query = $this->get_db()->prepare( "SELECT COUNT( DISTINCT log.book_id )
			FROM {$tbl_log} AS log 
			INNER JOIN {$tbl_term_r} AS tr ON( log.book_id = tr.book_id )
			INNER JOIN {$tbl_terms} AS t ON( tr.term_id = t.id )
			WHERE t.taxonomy = 'genre'
			AND log.book_id NOT IN(
				SELECT book_id 
				FROM {$tbl_term_r} AS tr2 
				INNER JOIN {$tbl_terms} AS t2 ON( tr2.term_id = t2.id )
				WHERE t2.name IN( {$genre_placeholders} )
			)
			AND log.date_finished IS NOT NULL
			{$this->get_date_condition( 'log.date_finished', 'log.date_finished' )}",
			$genres
		);

		//error_log( $query );

		$other_count = $this->get_db()->get_var( $query );

		if ( ! empty( $other_count ) ) {
			$other_row        = new \stdClass();
			$other_row->count = absint( $other_count );
			$other_row->name  = esc_html__( 'Other', 'book-database' );
			$results[]        = $other_row;
		}

		return $results;

	}
}
