<?php
/**
 * Series Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\add_book_series;
use function Book_Database\delete_book_series;
use function Book_Database\get_book_series;
use function Book_Database\get_book_series_by;
use function Book_Database\update_book_series;

/**
 * Class Series
 * @package Book_Database\REST_API\v1
 */
class Series extends Controller {

	protected $rest_base = 'series';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all series
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_items' ),
			'permission_callback' => array( $this, 'can_view' )
		) );

		// Add a new series.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_item' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'name'         => array(
					'required'          => true,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'slug'         => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'description'  => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_kses_post( $param );
					}
				),
				'number_books' => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) ? absint( $param ) : 0;
					}
				)
			)
		) );

		// Update an existing series.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_item' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'           => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$series = get_book_series_by( 'id', $param );

						return $series instanceof \Book_Database\Series;
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'name'         => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'slug'         => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'description'  => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_kses_post( $param );
					}
				),
				'number_books' => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) ? absint( $param ) : 0;
					}
				)
			)
		) );

		// Delete a series.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_item' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$series = get_book_series_by( 'id', $param );

						return $series instanceof \Book_Database\Series;
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				)
			)
		) );

		// Suggest series names.
		register_rest_route( $this->namespace, $this->rest_base . '/suggest', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'suggest' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'format' => array(
					'default'           => 'array',
					'validate_callback' => function ( $param, $request, $key ) {
						return in_array( $param, array( 'text', 'array' ) );
					}
				),
				'q'      => array(
					'required'          => true,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_strip_all_tags( $param );
					}
				)
			)
		) );

	}

	/**
	 * Get all book series
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'date_created',
				'order'   => 'ASC',
				'fields'  => array(
					'id',
					'name',
					'slug',
					'description',
					'number_books',
					'date_created',
					'date_modified'
				)
			) );

			$taxonomies = get_book_series( $args );

			return new \WP_REST_Response( $taxonomies );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add a new series
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_item( $request ) {

		try {

			$args = array(
				'name'         => $request->get_param( 'name' ) ?? '',
				'slug'         => $request->get_param( 'slug' ) ?? '',
				'description'  => $request->get_param( 'description' ) ?? '',
				'number_books' => $request->get_param( 'number_books' ) ?? 0
			);

			$series_id = add_book_series( $args );
			$series    = get_book_series_by( 'id', $series_id );

			if ( empty( $series ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new item from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $series->export_vars() );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update an existing series
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {

		try {

			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A series ID is required.', 'book-database' ), 400 );
			}

			$args = array();

			update_book_series( $id, $request->get_params() );

			$series = get_book_series_by( 'id', $id );

			return new \WP_REST_Response( $series->export_vars() );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete a series
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {

		try {

			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A series ID is required.', 'book-database' ), 400 );
			}

			delete_book_series( $id );

			return new \WP_REST_Response( true );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

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

			$format = $request->get_param( 'format' ) ?? 'array';

			$names = get_book_series( array(
				'search' => strtolower( $request->get_param( 'q' ) ),
				'fields' => 'name'
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