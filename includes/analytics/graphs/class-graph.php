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
class Graph {

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
			'type'    => $this->type,
			'data'    => array(
				'labels'   => array(),
				'datasets' => array(),
			),
			'options' => array(
				'title'  => array(
					'text' => ''
				),
				//'responsive'          => true,
				//'maintainAspectRatio' => false,
				'scales' => array(
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
				),
				'plugins' => array(
					'labels' => array(
						'render' => 'label',
						//'overlap' => false
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
	 */
	public function set_range( $start = '', $end = '' ) {
		try {
			$this->date_start = new \DateTime( $start );
			$this->date_end   = new \DateTime( $end );
			$this->date_start->setTimezone( get_site_timezone() );
			$this->date_end->setTimezone( get_site_timezone() );

			$this->set_timestamps();;
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
			error_log(var_export($datetime, true));

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
					$datetime->setDate( $datetime->format( 'Y' ), $datetime->format( 'm' ), 1 )->setTime( 0, 0, 0 );
					break;

				default :
					$datetime->setTime( 0, 0, 0 );
					break;
			}

			$timestamp = $datetime->getTimestamp();
			error_log(var_export($datetime, true));
			error_log($timestamp);

			if ( isset( $final_dates[ $timestamp ] ) ) {
				$final_dates[ $timestamp ] = $value;
			}
		}

		return array_values( $final_dates );

	}

	/**
	 * Add a data set
	 *
	 * @param array $args            Dataset args.
	 * @param array $data            Data.
	 * @param bool  $auto_fill       Whether to auto fill the dataset with the label values.
	 * @param bool  $colour_per_data Whether or not to have a different colour for each piece of data.
	 */
	public function add_dataset( $args = array(), $data = array(), $auto_fill = true, $colour_per_data = false ) {

		$rgb = array();
		foreach ( array( 'r', 'g', 'b' ) as $colour ) {
			$rgb[ $colour ] = mt_rand( 0, 255 );
		}

		if ( $colour_per_data ) {
			$background = array();
			$border     = array();

			for ( $i = 0; $i < count( $data ); $i++ ) {
				$rgb = $this->random_colour();

				// If we already have this one, get another.
				if ( in_array( 'rgb(' . $rgb . ')', $border ) ) {
					$new_rgb = array();
					foreach ( array( 'r', 'g', 'b' ) as $colour ) {
						$new_rgb[ $colour ] = mt_rand( 0, 255 );
					}
					$rgb = implode( ', ', $new_rgb );
				}

				$background[] = 'rgba(' . $rgb . ',0.3)';
				$border[]     = 'rgb(' . $rgb . ')';
			}
		} else {
			$rgb        = $this->random_colour();
			$background = 'rgba(' . $rgb . ',0.5)';
			$border     = 'rgb(' . $rgb . ')';
		}

		$args = wp_parse_args( $args, array(
			'label'                => '',
			'backgroundColor'      => $background,
			'borderColor'          => $border,
			'fill'                 => true,
			'borderWidth'          => 1,
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

	protected function random_colour() {

		$colours = array(
			'54, 162, 235', // Blue
			'75, 192, 192', // Teal
			'86, 255, 97', // Green
			'153, 102, 255', // Purple
			//'201, 203, 207', // Grey
			'252, 108,18', // Orange (dark)
			'252, 221, 18', // Yellow (bright)
			'255, 86, 86', // Red
			'255, 99, 132', // Pink
			'255, 159, 64', // Orange (medium)
			'255, 205, 86', // Yellow (pale)
		);

		return $colours[ rand( 0, count( $colours ) - 1 ) ];

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