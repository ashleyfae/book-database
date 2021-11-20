<?php
/**
 * Book Term Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exceptions\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\get_book_terms;

/**
 * Class Book_Term
 * @package Book_Database\REST_API\v1
 */
class Book_Term extends Controller {

	protected $rest_base = 'book-term';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Suggest term names.
		register_rest_route( $this->namespace, $this->rest_base . '/suggest', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'suggest' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'taxonomy' => array(
					'required' => true
				),
				'format'   => array(
					'default'           => 'array',
					'validate_callback' => function ( $param, $request, $key ) {
						return in_array( $param, array( 'text', 'array' ) );
					}
				),
				'q'        => array(
					'required'          => true,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_strip_all_tags( $param );
					}
				)
			)
		) );

	}

	/**
	 * Suggest term names
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function suggest( $request ) {

		try {

			$format   = $request->get_param( 'format' ) ?? 'array';
			$taxonomy = $request->get_param( 'taxonomy' );

			if ( empty( $taxonomy ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A taxonomy slug is required.', 'book-database' ), 400 );
			}

			$names = get_book_terms( array(
				'taxonomy' => $taxonomy,
				'search'   => strtolower( $request->get_param( 'q' ) ),
				'fields'   => 'name'
			) );

			if ( 'text' === $format ) {
				$names = implode( "\n", $names );
			}

			return new \WP_REST_Response( $names );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}
