<?php
/**
 * Utility Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;

/**
 * Class Utility
 * @package Book_Database\REST_API\v1
 */
class Utility extends Controller {

	protected $rest_base = 'utility';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Convert dates.
		register_rest_route( $this->namespace, $this->rest_base . '/convert-date', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'convert_date' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'date' => array(
					'required' => true
				),
			)
		) );

	}

	/**
	 * Can edit
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function can_edit( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Convert date
	 *
	 * Converts a provided date string (that's in WordPress site time) to UTC and formats it in MySQL format.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function convert_date( $request ) {

		try {

			$date = $request->get_param( 'date' );

			if ( empty( $date ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A date ID is required.', 'book-database' ), 400 );
			}

			return new \WP_REST_Response( get_gmt_from_date( $date ) );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}