<?php
/**
 * Retailer Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\add_retailer;
use function Book_Database\delete_retailer;
use function Book_Database\get_retailer;
use function Book_Database\get_retailers;
use function Book_Database\update_retailer;

/**
 * Class Retailer
 * @package Book_Database\REST_API\v1
 */
class Retailer extends Controller {

	protected $rest_base = 'retailer';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all retailers.
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_retailers' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'number'  => array(
					'default'           => 20,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'orderby' => array(
					'default'           => 'id',
					'validate_callback' => function ( $param, $request, $key ) {
						return in_array( strtolower( $param ), array( 'id', 'name', 'date_created', 'date_modified' ) );
					},
				),
				'order'   => array(
					'default'           => 'ASC',
					'validate_callback' => function ( $param, $request, $key ) {
						return in_array( strtoupper( $param ), array( 'ASC', 'DESC' ) );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return 'ASC' === strtoupper( $param ) ? 'ASC' : 'DESC';
					}
				)
			)
		) );

		// Add a new retailer.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_retailer' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'name'     => array(
					'required'          => true,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'template' => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_kses_post( $param );
					}
				)
			)
		) );

		// Update an existing retailer.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_retailer' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'       => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$retailer = get_retailer( $param );

						return ! empty( $retailer );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'name'     => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'template' => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_kses_post( $param );
					}
				)
			)
		) );

		// Delete a retailer.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_retailer' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$retailer = get_retailer( $param );

						return ! empty( $retailer );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				)
			)
		) );

	}

	/**
	 * Get all retailers
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_retailers( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'id',
				'order'   => 'ASC',
				'number'  => 20
			) );

			$retailers = get_retailers( $args );

			$retailer_data = array();

			foreach ( $retailers as $retailer ) {
				$retailer_data[] = $retailer->export_vars();
			}

			return new \WP_REST_Response( $retailer_data );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add retailer
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_retailer( $request ) {

		try {
			$args = array(
				'name'     => $request->get_param( 'name' ),
				'template' => $request->get_param( 'template' )
			);

			$retailer_id = add_retailer( $args );
			$retailer    = get_retailer( $retailer_id );

			if ( empty( $retailer ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new retailer from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $retailer->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update a retailer
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_retailer( $request ) {

		try {
			$whitelist = array(
				'name',
				'template',
				'date_created',
				'date_modified'
			);

			$args = array();

			foreach ( $request->get_params() as $param_key => $param_value ) {
				if ( in_array( $param_key, $whitelist ) ) {
					$args[ $param_key ] = $param_value;
				}
			}

			update_retailer( $request->get_param( 'id' ), $args );
			$retailer = get_retailer( $request->get_param( 'id' ) );

			if ( empty( $retailer ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new retailer from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $retailer->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete a retailer
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_retailer( $request ) {

		try {
			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A retailer ID is required.', 'book-database' ), 400 );
			}

			delete_retailer( $id );

			return new \WP_REST_Response( true );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}