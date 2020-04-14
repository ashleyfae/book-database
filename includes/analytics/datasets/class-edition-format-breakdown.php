<?php
/**
 * Dataset: Edition Format Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;
use function Book_Database\get_book_formats;

/**
 * Class Edition_Format_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Edition_Format_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart( array(
			'series' => array(
				array(
					'type'       => 'PieSeries',
					'name'       => __( 'Edition Format Breakdown', 'book-database' ),
					'dataFields' => array(
						'category' => 'format',
						'value'    => 'count'
					),
					'slices'     => array(
						'tooltipText' => '{category}: {value.value} Books',
					),
				)
			),
			'legend' => array(
				'type'        => 'Legend',
			),
		) );

		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$query = "SELECT format, COUNT( id ) AS count
			FROM {$tbl_editions} 
			WHERE date_acquired IS NOT NULL
			{$this->get_date_condition( 'date_acquired', 'date_acquired' )} 
			GROUP BY format
			ORDER BY format DESC";

		$results = $this->get_db()->get_results( $query );

		$formats = get_book_formats();

		if ( ! empty( $results ) ) {
			foreach ( $results as $index => $result ) {
				$result->count = absint( $result->count );

				if ( is_null( $result->format ) || ! array_key_exists( $result->format, $formats ) ) {
					$result->format = __( 'Unknown', 'book-database' );
				} else {
					$result->format = esc_html( $formats[ $result->format ] );
				}

				$results[ $index ] = $result;
			}
		}

		$chart->add_dataset( $results );

		return $chart->get_args();

	}

}