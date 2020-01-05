<?php
/**
 * Dataset: Pages Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Pages_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Pages_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart();

		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$number_range = 200;

		$query = $this->get_db()->prepare(
			"SELECT CONCAT( %d * FLOOR( pages/%d ), '-', %d * FLOOR( pages/%d ) + %d ) AS page_range, COUNT(*) AS number_books
			FROM {$tbl_log} AS log 
			INNER JOIN {$tbl_books} AS book ON ( book.id = log.book_id )
			WHERE date_finished IS NOT NULL 
			AND book.pages IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )} 
			GROUP BY 1
			ORDER BY pages",
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range ),
			absint( $number_range - 1 )
		);

		//error_log( $query );

		$results = $this->get_db()->get_results( $query );

		$columns = array(
			array(
				'id'      => 'page_range',
				'label'   => esc_html__( 'Number of Pages', 'book-database' ),
				'type'    => 'string',
				'display' => esc_html__( '%s Pages', 'book-database' )
			),
			array(
				'id'      => 'number_books',
				'label'   => esc_html__( 'Number of Books', 'book-database' ),
				'type'    => 'number',
				'display' => esc_html__( '%d Books', 'book-database' )
			)
		);

		$chart->add_dataset( $columns, $results );

		return $chart->get_args();

	}
}