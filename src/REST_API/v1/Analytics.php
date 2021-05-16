<?php
/**
 * Analytics Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\Analytics\get_dataset_value;
use function Book_Database\Analytics\get_date_filter_range;
use function Book_Database\Analytics\get_dates_filter_options;
use function Book_Database\Analytics\set_current_date_filter;
use function Book_Database\format_date;

/**
 * Class Analytics
 *
 * @package Book_Database\REST_API\v1
 */
class Analytics extends Controller {

	protected $rest_base = 'analytics';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all analytics -- deprecated
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_analytics' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'start_date' => array(
					'default'           => date( 'Y-1-1 00:00:00' ),
					'sanitize_callback' => function ( $param, $request, $key ) {
						return date( 'Y-m-d 00:00:00', strtotime( $param ) );
					}
				),
				'end_date'   => array(
					'default'           => date( 'Y-1-1 23:59:59' ),
					'sanitize_callback' => function ( $param, $request, $key ) {
						return date( 'Y-m-d 23:59:59', strtotime( $param ) );
					}
				),
				'stats'      => array(
					'default'           => array(),
					'validate_callback' => function ( $param, $request, $key ) {
						return empty( $param ) || is_array( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? array() : array_map( 'sanitize_text_field', $param );
					},
				),
				'args'       => array(
					'default'           => array(),
					'validate_callback' => function ( $param, $request, $key ) {
						return empty( $param ) || is_array( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? array() : array_map( 'sanitize_text_field', $param );
					},
				)
			)
		) );

		// Get a dataset
		register_rest_route( $this->namespace, $this->rest_base . '/dataset/(?P<dataset>[a-zA-Z-_]+)', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_item' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'dataset'     => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return class_exists( '\\Book_Database\\Analytics\\Datasets\\' . $param );
					}
				),
				'date_option' => array()
			)
		) );

		// Set the date range.
		register_rest_route( $this->namespace, $this->rest_base . '/range', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'set_dates' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'option' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return array_key_exists( $param, get_dates_filter_options() );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( strtolower( $param ) );
					}
				),
				'start'  => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return date( 'Y-m-d H:i:s', strtotime( $param ) );
					}
				),
				'end'    => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return date( 'Y-m-d H:i:s', strtotime( $param ) );
					}
				)
			)
		) );

	}

	/**
	 * Get Analytics
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_analytics( $request ) {

		try {

			$stat_types = $request->get_param( 'stats' );
			$stats      = array();
			$analytics  = new \Book_Database\Analytics( $request->get_param( 'start_date' ), $request->get_param( 'end_date' ), $request->get_param( 'args' ) );

			foreach ( $stat_types as $stat_type ) {
				$method = 'get_' . str_replace( '-', '_', $stat_type );

				if ( method_exists( $analytics, $method ) ) {
					$stats[ $stat_type ] = call_user_func( array( $analytics, $method ) );
				} elseif ( false !== strpos( $stat_type, 'taxonomy' ) ) {
					preg_match( '/(?<=taxonomy-)(.*)(?=-breakdown)/', $stat_type, $matches );
					$stats[ $stat_type ] = call_user_func( array( $analytics, 'get_taxonomy_breakdown' ), $matches[0] );
				} else {
					$stats[ $stat_type ] = null;
				}
			}

			return new \WP_REST_Response( $stats );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Get a single dataset value
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_item( $request ) {

		try {
			return new \WP_REST_Response( get_dataset_value( $request->get_param( 'dataset' ), $request->get_params() ) );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Save the date filter
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function set_dates( $request ) {

		try {

			set_current_date_filter( $request->get_params() );

			$range = get_date_filter_range();

			$range['start_formatted'] = format_date( $range['start'] );
			$range['end_formatted']   = format_date( $range['end'] );

			return new \WP_REST_Response( $range );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}
