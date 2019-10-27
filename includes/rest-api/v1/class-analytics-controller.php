<?php
/**
 * Analytics Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;

/**
 * Class Analytics
 * @package Book_Database\REST_API\v1
 */
class Analytics extends Controller {

	protected $rest_base = 'analytics';

	/**
	 * Register routes
	 */
	public function register_routes() {

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

}