<?php
/**
 * Dataset: Edition Genre Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Edition_Genre_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Edition_Genre_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart( array(
			'series' => array(
				array(
					'type'       => 'PieSeries',
					'name'       => __( 'Edition Genre Breakdown', 'book-database' ),
					'dataFields' => array(
						'category' => 'genre',
						'value'    => 'number_editions'
					),
					'slices'     => array(
						'tooltipText' => '{category}: {value.value} Books',
					),
				)
			),
			'legend' => array(
				'type' => 'Legend',
			),
		) );

		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();
		$tbl_term_r   = book_database()->get_table( 'book_term_relationships' )->get_table_name();
		$tbl_terms    = book_database()->get_table( 'book_terms' )->get_table_name();

		$query = "SELECT COUNT( DISTINCT edition.id ) AS number_editions, t.name AS genre
		FROM {$tbl_editions} AS edition
		INNER JOIN {$tbl_term_r} AS tr ON( edition.book_id = tr.book_id )
		INNER JOIN {$tbl_terms} AS t ON( tr.term_id = t.id )
		WHERE t.taxonomy = 'genre'
		AND edition.date_acquired IS NOT NULL 
		{$this->get_date_condition( 'date_acquired', 'date_acquired' )}
		GROUP BY t.name";

		$this->log( $query, __CLASS__ );

		$results = $this->get_db()->get_results( $query );

		$chart->add_dataset( $results );

		return $chart->get_args();

	}
}