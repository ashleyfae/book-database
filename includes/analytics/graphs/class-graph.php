<?php
/**
 * Graph
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\get_site_timezone;

/**
 * Class Graph
 *
 * @package Book_Database\Analytics
 */
class Graph {

	/**
	 * @var string Type of graph.
	 */
	protected $type = 'PieChart';

	/**
	 * @var string Type of series.
	 */
	protected $series_type = 'PieSeries';

	/**
	 * @var array Graph arguments.
	 */
	protected $args = array();

	/**
	 * @var \DateTime Start of the graph range.
	 */
	protected $date_start;

	/**
	 * @var \DateTime End of the graph range.
	 */
	protected $date_end;

	/**
	 * @var string Interval used in date charts.
	 */
	protected $interval = 'day';

	/**
	 * @var string Timestamp key format.
	 */
	protected $timestamp_format = 'U';

	/**
	 * @var array All collected timestamps.
	 */
	protected $timestamps = array();

	/**
	 * Graph constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		$this->args = array(
			'type'  => $this->type,
			'chart' => wp_parse_args( $args, array(
				'exporting' => array(
					'menu'       => array(
						'items' => array(
							array(
								'label' => '...',
								'menu'  => array(
									array(
										'label' => __( 'Image', 'book-database' ),
										'menu'  => array(
											array( 'type' => 'png', 'label' => __( 'PNG', 'book-database' ) ),
											array( 'type' => 'jpg', 'label' => __( 'JPG', 'book-database' ) ),
											array( 'type' => 'svg', 'label' => __( 'SVG', 'book-database' ) )
										)
									),
									array(
										'label' => __( 'Data', 'book-database' ),
										'menu'  => array(
											array( 'type' => 'json', 'label' => __( 'JSON', 'book-database' ) ),
											array( 'type' => 'csv', 'label' => __( 'CSV', 'book-database' ) )
										)
									),
									array(
										'label' => __( 'Print', 'book-database' ),
										'type'  => 'print'
									)
								)
							)
						)
					),
					'filePrefix' => 'book-database'
				)
			) )
		);

	}

	/**
	 * Set the start and end dates for the graph
	 *
	 * @param string $start
	 * @param string $end
	 */
	public function set_range( $start = '', $end = '' ) {
		try {
			$this->date_start = new \DateTime( $start );
			$this->date_end   = new \DateTime( $end );
			$this->date_start->setTimezone( get_site_timezone() );
			$this->date_end->setTimezone( get_site_timezone() );
		} catch ( \Exception $e ) {

		}
	}

	/**
	 * Set the array of all available timestamps - this is our x-axis
	 *
	 * Given the start and end date, this determines the interval (hour, day, or month), and then creates
	 * an array of all timestamps for each period within.
	 *
	 * @throws \Exception
	 */
	public function set_timestamps() {

		if ( ! $this->date_start instanceof \DateTime || ! $this->date_end instanceof \DateTime ) {
			throw new \Exception( __( 'Start and end dates not configured.', 'book-database' ), 400 );
		}

		$diff = $this->date_start->diff( $this->date_end );

		if ( $diff->days >= 65 ) {
			$this->interval         = 'month';
			$period                 = new \DatePeriod( $this->date_start, new \DateInterval( 'P1M' ), $this->date_end );
			$this->timestamp_format = 'M Y';
		} else {
			$this->interval         = 'day';
			$period                 = new \DatePeriod( $this->date_start, new \DateInterval( 'P1D' ), $this->date_end );
			$this->timestamp_format = 'j M Y';
		}

		foreach ( $period as $datetime ) {
			/**
			 * @var \DateTime $datetime
			 */

			$this->timestamps[] = $datetime->format( $this->timestamp_format );
		}

	}

	/**
	 * Get the interval
	 *
	 * @return string
	 */
	public function get_interval() {
		return $this->interval;
	}

	/**
	 * Fill data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function fill_data( $data = array() ) {

		$final_dates = array_flip( $this->timestamps );
		$final_dates = array_fill_keys( array_keys( $final_dates ), 0 );

		foreach ( $data as $date => $value ) {
			try {
				$datetime = new \DateTime( $date );
			} catch ( \Exception $e ) {
				continue;
			}

			$datetime->setTimezone( get_site_timezone() );

			switch ( $this->interval ) {
				case 'month' :
					$datetime->setDate( $datetime->format( 'Y' ), $datetime->format( 'm' ), 1 )->setTime( 0, 0, 0 );
					break;

				default :
					$datetime->setTime( 0, 0, 0 );
					break;
			}

			$timestamp = $datetime->format( $this->timestamp_format );

			if ( isset( $final_dates[ $timestamp ] ) ) {
				$final_dates[ $timestamp ] = $value;
			}
		}

		return $final_dates;

	}

	/**
	 * Add a data set
	 *
	 * @param array $series Series data.
	 * @param array $data   Data.
	 */
	public function add_dataset( $data = array() ) {
		$this->args['chart']['data'] = $this->shape_data( $data );
	}

	/**
	 * Shape data
	 *
	 * Format and sanitize.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function shape_data( $data ) {
		return $data;
	}

	/**
	 * Get all the arguments
	 *
	 * @return array
	 */
	public function get_args() {
		return $this->args;
	}

}