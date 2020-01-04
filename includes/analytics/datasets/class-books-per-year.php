<?php
/**
 * class-books-per-year.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use DateInterval;
use DatePeriod;
use DateTime;
use function Book_Database\book_database;

/**
 * Class Books_Per_Year
 *
 * @package Book_Database\Analytics
 */
class Books_Per_Year extends Dataset {

	protected $type = 'graph';

	/**
	 * Calculate books per year
	 *
	 * @return array Array of Chart settings.
	 */
	protected function _get_dataset() {

		$graph = new Horizontal_Bar_Graph();

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$years    = array();
		$raw_rows = $this->get_db()->get_results(
			"SELECT YEAR( date_finished ) AS year, COUNT(*) AS number_books
			FROM {$tbl_log}
			WHERE date_finished IS NOT NULL
			GROUP BY year
			ORDER BY year DESC;"
		);

		if ( empty( $raw_rows ) ) {
			return $graph->get_args();
		}

		$first_year = $raw_rows[ count( $raw_rows ) - 1 ]->year ?? false;
		$last_year  = date( 'Y' ) + 1; // We add +1 because DatePeriod doesn't include the end date.

		// If possible, fill up our array with default values.
		if ( ! empty( $first_year ) && ! empty( $last_year ) ) {
			try {
				$period = new DatePeriod( new DateTime( sprintf( '%d-01-01', $first_year ) ), new DateInterval( 'P1Y' ), new DateTime( sprintf( '%d-01-01', $last_year ) ) );

				foreach ( $period as $datetime ) {
					/**
					 * @var DateTime $datetime
					 */
					$years[ $datetime->format( 'Y' ) ] = 0;
				}
			} catch ( \Exception $e ) {

			}
		}

		// Now fill up with our legit values.
		foreach ( $raw_rows as $row ) {
			$years[ absint( $row->year ) ] = absint( $row->number_books );
		}

		ksort( $years, SORT_NUMERIC );

		$args = array(
			'label'           => __( 'Books Read', 'book-database' ),
			'backgroundColor' => array(),
			'borderColor'     => array(),
			'fill'            => false
		);

		for ( $i = 0; $i < count( $years ); $i++ ) {
			$rgb = array();
			foreach ( array( 'r', 'g', 'b' ) as $colour ) {
				$rgb[ $colour ] = mt_rand( 0, 255 );
			}

			$args['backgroundColor'][] = 'rgba(' . implode( ', ', $rgb ) . ',0.5)';
			$args['borderColor'][]     = 'rgb(' . implode( ', ', $rgb ) . ')';
		}

		$graph->add_dataset( $args, array_values( $years ), false );
		$graph->set_labels( array_map( 'strval', array_keys( $years ) ) );

		return $graph->get_args();

	}
}