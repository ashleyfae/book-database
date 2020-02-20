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

	/**
	 * @var string Timestamp key format.
	 */
	protected $timestamp_format = 'U';

	protected $timestamps = array();

	/**
	 * Graph constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		$this->args = wp_parse_args( $args, array(
			'type'    => $this->type,
			'options' => array(
				'backgroundColor' => 'transparent',
				'chartArea'       => array(
					'width'  => '100%',
					'height' => '80%'
				),
				'legend'          => array(
					'position' => 'top'
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

		if ( $diff->days >= 230 ) {
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

			$this->timestamps[]             = $datetime->format( $this->timestamp_format );
			$this->args['data']['labels'][] = $datetime->format( $this->timestamp_format );
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
	 * @param array $columns Column labels.
	 * @param array $rows    Rows values.
	 */
	public function add_dataset( $columns = array(), $rows = array() ) {

		$this->args['chart']['cols'] = $this->shape_columns( $columns );
		$this->args['chart']['rows'] = $this->shape_rows( $columns, $rows );

	}

	/**
	 * Shape columns
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	protected function shape_columns( $columns ) {

		foreach ( $columns as $key => $value ) {
			unset( $columns[ $key ]['display'] );
		}

		return $columns;

	}

	/**
	 * Shape rows
	 *
	 * Format and sanitize.
	 *
	 * @param array $columns
	 * @param array $rows
	 *
	 * @return array
	 */
	protected function shape_rows( $columns, $rows ) {

		$formatted_rows = array();

		foreach ( $rows as $row ) {
			$values = array();

			foreach ( $columns as $column ) {
				$this_value = array(
					'v' => isset( $row->{$column['id']} ) ? $this->sanitize_row_value( $row->{$column['id']}, $column['type'] ) : null
				);

				if ( ! empty( $column['display'] ) ) {
					$this_value['f'] = sprintf( $column['display'], $this_value['v'] );
				}

				$values[] = $this_value;
			}

			$formatted_rows[] = array(
				'c' => $values
			);
		}

		return $formatted_rows;

	}

	protected function sanitize_row_value( $value, $type ) {
		switch ( $type ) {
			case 'number' :
				return is_int( $value ) ? intval( $value ) : floatval( $value );
				break;

			case 'date' :
				return 'new Date( ' . esc_attr( sanitize_text_field( $value ) ) . ' )';
				break;

			default :
				return sanitize_text_field( $value );
				break;
		}
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