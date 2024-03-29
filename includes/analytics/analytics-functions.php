<?php
/**
 * Analytics Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use Book_Database\Exceptions\Exception;
use function Book_Database\get_site_timezone;

/**
 * Get the available date filter options
 *
 * @return array
 */
function get_dates_filter_options() {
	return array(
		'last_30_days' => __( 'Last 30 Days', 'book-database' ),
		'this_month'   => __( 'This Month', 'book-database' ),
		'last_month'   => __( 'Last Month', 'book-database' ),
		'this_year'    => __( 'This Year', 'book-database' ),
		'last_year'    => __( 'Last Year', 'book-database' ),
		'all_time'     => __( 'All Time', 'book-database' ),
		'custom'       => __( 'Custom', 'book-database' ),
	);
}

/**
 * Get the saved date filter
 *
 * Note that `start` and `end` will only be filled out if the option is `custom`.
 *
 * @return array
 */
function get_current_date_filter() {

	$dates = get_option( 'bdb_reports_date_filter' );

	if ( ! is_array( $dates ) ) {
		$dates = array();
	}

	return wp_parse_args( $dates, array(
		'option' => 'last_30_days',
		'start'  => '',
		'end'    => ''
	) );

}

/**
 * Set the current date filter
 *
 * @param array $args
 */
function set_current_date_filter( $args = array() ) {

	$defaults = array(
		'option' => 'last_30_days',
		'start'  => '',
		'end'    => ''
	);

	$args = wp_parse_args( $args, $defaults );

	update_option( 'bdb_reports_date_filter', array_intersect_key( $args, $defaults ), false );

}

/**
 * Get the start and end date range for a filter option.
 *
 * @param string $option Date filter option key. @see get_dates_filter_options()
 * @param string $format Final date format.
 *
 * @return array
 */
function get_date_filter_range( $option = '', $format = 'Y-m-d H:i:s' ) {

	try {

		$timezone_utc = new \DateTimeZone( 'UTC' );
		$saved_filter = get_current_date_filter();

		if ( empty( $option ) ) {
			$option = $saved_filter['option'] ?? 'last_30_days';
		}

		$range = array(
			'start' => '',
			'end'   => new \DateTime( 'now', get_site_timezone() )
		);

		switch ( $option ) {

			case 'last_30_days' :
				$range['start'] = new \DateTime( '-30 days', get_site_timezone() );
				break;

			case 'this_month' :
				$range['start'] = new \DateTime( 'first day of this month', get_site_timezone() );
				$range['end']   = new \DateTime( 'last day of this month', get_site_timezone() );
				break;

			case 'last_month' :
				$range['start'] = new \DateTime( 'first day of last month', get_site_timezone() );
				$range['end']   = new \DateTime( 'last day of last month', get_site_timezone() );
				break;

			case 'this_year' :
				$range['start'] = new \DateTime( date( 'Y-01-01' ), get_site_timezone() );
				$range['end']   = new \DateTime( date( 'Y-12-31' ), get_site_timezone() );
				break;

			case 'last_year' :
				$range['start'] = new \DateTime( 'January 1, last year', get_site_timezone() );
				$range['end']   = new \DateTime( 'December 31, last year', get_site_timezone() );
				break;

			case 'all_time' :
				$range['start'] = '';
				$range['end']   = '';
				break;

			case 'custom' :
				if ( isset( $saved_filter['start'] ) && isset( $saved_filter['end'] ) ) {
					$range['start'] = new \DateTime( $saved_filter['start'], get_site_timezone() );
					$range['end']   = new \DateTime( $saved_filter['end'], get_site_timezone() );
				}
				break;

		}

		/**
		 * Start always at 00:00:00
		 * End always at 23:59:59
		 * Convert to UTC
		 * Format
		 */
		if ( $range['start'] instanceof \DateTime ) {
			$range['start'] = $range['start']->setTime( 0, 0, 0 )->setTimezone( $timezone_utc )->format( $format );
		}
		if ( $range['end'] instanceof \DateTime ) {
			$range['end'] = $range['end']->setTime( 23, 59, 59 )->setTimezone( $timezone_utc )->format( $format );
		}

	} catch ( \Exception $e ) {
		$range = array(
			'start' => '',
			'end'   => ''
		);
	}

	return $range;

}

/**
 * Get a dataset value by name
 *
 * @param string $dataset Dataset class name.
 * @param array  $args    Arguments to pass to the class.
 *
 * @return array
 * @throws Exception
 */
function get_dataset_value( $dataset, $args = array() ) {
	$class_name = __NAMESPACE__ . '\\Datasets\\' . $dataset;

	if ( class_exists( $class_name ) ) {
		$dataset = new $class_name( $args );

		/**
		 * @var Dataset $dataset
		 */
		return $dataset->get_dataset();
	}

	throw new Exception( 'invalid-dataset', __( 'Invalid dataset.', 'book-database' ), 404 );
}
