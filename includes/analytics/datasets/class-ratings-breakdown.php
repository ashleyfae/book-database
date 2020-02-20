<?php
/**
 * Dataset: Ratings Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Ratings_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Ratings_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart();

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT rating, COUNT( IFNULL( rating, 1 ) ) AS count
			FROM {$tbl_log} AS log 
			WHERE date_finished IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )} 
			GROUP BY rating
			ORDER BY rating DESC";

		$results   = $this->get_db()->get_results( $query );

		$columns = array(
			array(
				'id'      => 'rating',
				'label'   => esc_html__( 'Rating', 'book-database' ),
				'type'    => 'string',
				'display' => esc_html__( '%s Stars', 'book-database' )
			),
			array(
				'id'      => 'count',
				'label'   => esc_html__( 'Number of Books', 'book-database' ),
				'type'    => 'number',
				'display' => esc_html__( '%d Books', 'book-database' )
			)
		);

		if ( ! empty( $results ) ) {
			foreach ( $results as $index => $result ) {
				$result->rating = is_null( $result->rating ) ? __( 'None', 'book-database' ) : sprintf( __( '%s Stars', 'book-database' ), $result->rating * 1 );
				$result->count  = absint( $result->count );

				$results[ $index ] = $result;
			}
		}

		$chart->add_dataset( $columns, $results );

		return $chart->get_args();

	}
}