<?php
/**
 * Base Dataset Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Class Dataset
 *
 * @package Book_Database\Analytics
 */
abstract class Dataset {

	/**
	 * @var string Dataset type:
	 *             value - Single value
	 *             dataset - Group of values
	 *             table - Table
	 *             graph - Chart.js graph
	 */
	protected $type = 'value';

	/**
	 * @var string Start date for the query.
	 */
	protected $date_start = '';

	/**
	 * @var string End date for the query.
	 */
	protected $date_end = '';

	/**
	 * @var array Raw arguments.
	 */
	protected $args = array();

	/**
	 * Dataset constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {

		//$this->log( var_export( $args, true ), __METHOD__ );

		$defaults = array(
			'date_option' => get_current_date_filter()['option'],
			'start'       => '',
			'end'         => ''
		);

		if ( 'custom' !== $defaults['date_option'] && array_key_exists( $defaults['date_option'], get_dates_filter_options() ) ) {
			$dates             = get_date_filter_range( $defaults['date_option'] );
			$defaults['start'] = $dates['start'] ?? '';
			$defaults['end']   = $dates['end'] ?? '';
		}

		$args = wp_parse_args( $args, $defaults );

		if ( 'all_time' === $args['date_option'] ) {
			$args['start'] = '';
			$args['end']   = '';
		}

		//$this->log( sprintf( '%s Dataset Args: %s', __CLASS__, var_export( $args, true ) ), __METHOD__ );

		$this->date_start = $args['start'];
		$this->date_end   = $args['end'];
		$this->args       = $args;

	}

	/**
	 * Get the dataset result.
	 *
	 * The data may either be a single value or table/graph data.
	 *
	 * @return array
	 */
	public function get_dataset() {
		return array(
			'type' => $this->type,
			'data' => $this->_get_dataset()
		);
	}

	/**
	 * Get the dataset.
	 *
	 * @return mixed
	 */
	abstract protected function _get_dataset();

	/**
	 * Get the database object
	 *
	 * @return \wpdb
	 */
	protected function get_db() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Get the selected date range condition
	 *
	 * @param string $start
	 * @param string $end
	 *
	 * @return string
	 */
	protected function get_date_condition( $start = 'date_created', $end = 'date_created' ) {

		$condition = array();

		if ( ! empty( $this->date_start ) ) {
			$condition[] = $this->get_db()->prepare( "{$start} >= %s", $this->date_start );
		}
		if ( ! empty( $this->date_end ) ) {
			$condition[] = $this->get_db()->prepare( "{$end} <= %s", $this->date_end );
		}

		$condition = implode( " AND ", $condition );

		if ( ! empty( $condition ) ) {
			return " AND " . $condition;
		}

		return '';

	}

	/**
	 * Log a message
	 *
	 * This only actually logs if WP_DEBUG is enabled.
	 *
	 * @param string $message Message to log.
	 * @param string $method  Method name.
	 */
	protected function log( $message, $method ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( "%s:\n%s", $method, $message ) );
		}
	}

}