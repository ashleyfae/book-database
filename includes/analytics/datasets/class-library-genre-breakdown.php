<?php
/**
 * Dataset: Library Genre Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Library_Genre_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Library_Genre_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart( array(
			'series' => array(
				array(
					'type'       => 'PieSeries',
					'name'       => __( 'Library Genre Breakdown', 'book-database' ),
					'dataFields' => array(
						'category' => 'genre',
						'value'    => 'number_books'
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

		$tbl_books  = book_database()->get_table( 'books' )->get_table_name();
		$tbl_term_r = book_database()->get_table( 'book_term_relationships' )->get_table_name();
		$tbl_terms  = book_database()->get_table( 'book_terms' )->get_table_name();

		$query = "SELECT COUNT( DISTINCT book.id ) AS number_books, t.name AS genre
		FROM {$tbl_books} AS book
		INNER JOIN {$tbl_term_r} AS tr ON( book.id = tr.book_id )
		INNER JOIN {$tbl_terms} AS t ON( tr.term_id = t.id )
		WHERE t.taxonomy = 'genre'
		{$this->get_date_condition( 'book.date_created', 'book.date_created' )}
		GROUP BY t.name
		LIMIT 10";

		$this->log( $query, __CLASS__ );

		$results = $this->get_db()->get_results( $query );

		$chart->add_dataset( $results );

		return $chart->get_args();

	}

}