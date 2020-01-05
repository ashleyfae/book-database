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
		$breakdown = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$key = is_null( $result->rating ) ? __( 'None', 'book-database' ) : sprintf( __( '%s Stars', 'book-database' ), $result->rating * 1 );

				$breakdown[ $key ] = absint( $result->count );
			}
		}

		$args = array(
			'label' => __( 'Rating Breakdown', 'book-database' ),
			//'fill'  => false
		);

		$chart->add_dataset( $args, array_values( $breakdown ), false, true );
		$chart->set_labels( array_map( 'strval', array_keys( $breakdown ) ) );

		return $chart->get_args();

	}
}