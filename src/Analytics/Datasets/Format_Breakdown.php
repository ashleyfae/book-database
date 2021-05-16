<?php
/**
 * Dataset: Format Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use Book_Database\Analytics\Pie_Chart;
use function Book_Database\book_database;
use function Book_Database\get_book_formats;

/**
 * Class Format_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Format_Breakdown extends Dataset {

	protected $type = 'graph';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$chart = new Pie_Chart( array(
			'series' => array(
				array(
					'type'       => 'PieSeries',
					'name'       => __( 'Format Breakdown', 'book-database' ),
					'dataFields' => array(
						'category' => 'format',
						'value'    => 'number_books'
					),
					'slices'     => array(
						'tooltipText' => __( '{category}: {value.value} Books', 'book-database' ),
					),
				)
			),
			'legend' => array(
				'type' => 'Legend',
			),
		) );

		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$query = "SELECT format, COUNT( log.id ) AS number_books
			FROM {$tbl_log} AS log 
			LEFT JOIN {$tbl_editions} AS edition ON( log.edition_id = edition.id )
			WHERE date_finished IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )} 
			GROUP BY format
			ORDER BY format DESC";

		//error_log( $query );

		$results = $this->get_db()->get_results( $query );

		$formats = get_book_formats();

		if ( ! empty( $results ) ) {
			foreach ( $results as $index => $result ) {
				$result->number_books = absint( $result->number_books );

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
