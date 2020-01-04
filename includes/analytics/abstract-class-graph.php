<?php
/**
 * ChartJS Graph
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
abstract class Graph {

	/**
	 * @var string Type of graph.
	 */
	protected $type = 'line';

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

	protected $interval = 'day';

	protected $timestamps = array();

	/**
	 * Graph constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		$this->args = wp_parse_args( $args, array(
			'type' => $this->type,
			'data' => array(
				'labels'   => array(),
				'datasets' => array(),
				'options'  => array(
					'title'               => array(
						'text' => ''
					),
					'responsive'          => true,
					'maintainAspectRatio' => false,
					'scales'              => array(
						'xAxes' => array(
							array(
								'ticks' => array(
									'autoSkipPadding' => 10,
									'maxLabels'       => 52,
									'min'             => 0,
									'precision'       => 0,
								)
							)
						),
						'yAxes' => array(
							array(
								'ticks' => array(
									'min'       => 0,
									'precision' => 0
								)
							)
						)
					)
				)
			)
		) );

	}

	/**
	 * Set the start and end dates for the graph
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @throws \Exception
	 */
	public function set_range( $start = '', $end = '' ) {
		$this->date_start = new \DateTime( $start );
		$this->date_end   = new \DateTime( $end );
		$this->date_start->setTimezone( get_site_timezone() );
		$this->date_end->setTimezone( get_site_timezone() );
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

		if ( $diff->days <= 2 ) {
			$this->interval = 'hour';
			$period         = new \DatePeriod( $this->date_start, new \DateInterval( 'P1H' ), $this->date_end );
			$format         = 'g:i A';
		} elseif ( $diff->days >= 230 ) {
			$this->interval = 'month';
			$period         = new \DatePeriod( $this->date_start, new \DateInterval( 'P1M' ), $this->date_end );
			$format         = 'M Y';
		} else {
			$this->interval = 'day';
			$period         = new \DatePeriod( $this->date_start, new \DateInterval( 'P1D' ), $this->date_end );
			$format         = 'j M Y';
		}

		foreach ( $period as $datetime ) {
			/**
			 * @var \DateTime $datetime
			 */

			$this->timestamps[]             = $datetime->getTimestamp();
			$this->args['data']['labels'][] = $datetime->format( $format );
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
	 * Set the graph labels
	 *
	 * @param array $labels
	 */
	public function set_labels( $labels = array() ) {
		$this->args['data']['labels'] = $labels;
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
				case 'hour' :
					$datetime->setTime( $datetime->format( 'H' ), 0, 0 );
					break;

				case 'month' :
					$datetime->setDate( $datetime->format( 'Y' ), $datetime->format( 'M' ), 1 )->setTime( 0, 0, 0 );
					break;

				default :
					$datetime->setTime( 0, 0, 0 );
					break;
			}

			$timestamp = $datetime->getTimestamp();

			if ( isset( $final_dates[ $timestamp ] ) ) {
				$final_dates[ $timestamp ] = $value;
			}
		}

		return array_values( $final_dates );

	}

	/**
	 * Add a data set
	 *
	 * @param array $args      Dataset args.
	 * @param array $data      Data.
	 * @param bool  $auto_fill Whether to auto fill the dataset with the label values.
	 */
	public function add_dataset( $args = array(), $data = array(), $auto_fill = true ) {

		$rgb = array();
		foreach ( array( 'r', 'g', 'b' ) as $colour ) {
			$rgb[ $colour ] = mt_rand( 0, 255 );
		}

		$args = wp_parse_args( $args, array(
			'label'                => '',
			'backgroundColor'      => 'rgba(252,108,18,0.5)',
			'borderColor'          => 'rgb(252,108,18)',
			'fill'                 => true,
			'borderDash'           => array( 2, 6 ),
			'borderCapStyle'       => 'round',
			'borderJoinStyle'      => 'round',
			'pointRadius'          => 4,
			'pointHoverRadius'     => 6,
			'pointBackgroundColor' => 'rgb(255,255,255)',
		) );

		$args['data'] = $auto_fill ? $this->fill_data( $data ) : $data;

		$this->args['data']['datasets'][] = $args;

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