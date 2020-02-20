<?php
/**
 * Dataset: Format Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

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

		$chart = new Pie_Chart();

		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$query = "SELECT format, COUNT( log.id ) AS count
			FROM {$tbl_log} AS log 
			LEFT JOIN {$tbl_editions} AS edition ON( log.edition_id = edition.id )
			WHERE date_finished IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )} 
			GROUP BY format
			ORDER BY format DESC";

		error_log( $query );

		$results = $this->get_db()->get_results( $query );

		$columns = array(
			array(
				'id'    => 'format',
				'label' => esc_html__( 'Format', 'book-database' ),
				'type'  => 'string',
			),
			array(
				'id'      => 'count',
				'label'   => esc_html__( 'Number of Books', 'book-database' ),
				'type'    => 'number',
				'display' => esc_html__( '%d Books', 'book-database' )
			)
		);

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

		$chart->add_dataset( $columns, $results );

		return $chart->get_args();

	}

}