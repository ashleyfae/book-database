<?php
/**
 * Dataset: Books Read by Publication Year
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Books_Read_by_Publication_Year
 *
 * @package Book_Database\Analytics
 */
class Books_Read_by_Publication_Year extends Dataset {

	protected $type = 'graph';

	/**
	 * Graph books read by publication year
	 *
	 * @return array Array of Chart settings.
	 */
	protected function _get_dataset() {

		$chart = new Scatter_Chart( array(
			//'cursor' => array(),
			'series' => array(
				array(
					'type'          => 'LineSeries',
					'name'          => __( 'Books Read by Publication Year', 'book-database' ),
					'dataFields'    => array(
						'categoryX' => 'date_finished',
						'valueY'    => 'publication_year'
					),
					'strokeOpacity' => 0,
					'bullets'       => array(
						array(
							'type'        => 'CircleBullet',
							'tooltipText' => __( "[bold]{title}:[/]\nDate Read: {categoryX}\nPublication Year: {valueY}", 'book-database' ),
							'circle'      => array(
								'radius'        => 10,
								'fillOpacity'   => 0.7,
								'strokeOpacity' => 0
							),
							'states'      => array(
								'hover' => array(
									'properties' => array(
										'fillOpacity' => 1,
										'scale'       => 1.7
									)
								)
							)
						)
					),
					'heatRules'     => array(
						array(
							'target'   => 'bullet',
							'property' => 'radius',
							'min'      => 2,
							'max'      => 60
						)
					)
				)
			),
			'yAxes'  => array(
				array(
					'type'            => 'ValueAxis',
					'title'           => array(
						'text' => __( 'Publication Year', 'book-database' )
					),
					'maxPrecision'    => 0,
					'numberFormatter' => array(
						'type'         => 'NumberFormatter',
						'numberFormat' => '#'
					)
				)
			),
			'xAxes'  => array(
				array(
					'type'       => 'CategoryAxis',
					'title'      => array(
						'text' => __( 'Date Read', 'book-database' )
					),
					'dataFields' => array(
						'category' => 'date_finished'
					),
				)
			)
		) );

		$tbl_books = book_database()->get_table( 'books' )->get_table_name();
		$tbl_log   = book_database()->get_table( 'reading_log' )->get_table_name();

		$query = "SELECT DATE_FORMAT( date_finished, '%Y-%m-%d' ) AS date_finished, YEAR(pub_date) AS publication_year, title
			FROM {$tbl_log} AS log
			INNER JOIN {$tbl_books} AS book ON( book.id = log.book_id )
			WHERE date_finished IS NOT NULL
			{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		$this->log( $query, __CLASS__ );

		$chart->add_dataset( $this->get_db()->get_results( $query ) );

		return $chart->get_args();

	}

}