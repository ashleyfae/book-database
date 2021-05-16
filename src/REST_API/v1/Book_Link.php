<?php
/**
 * Book Link Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\add_book_link;
use function Book_Database\delete_book_link;
use function Book_Database\get_book_link;
use function Book_Database\get_book_links;
use function Book_Database\update_book_link;

/**
 * Class Book_Link
 * @package Book_Database\REST_API\v1
 */
class Book_Link extends Controller {

	protected $rest_base = 'book-link';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all links.
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_items' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'book_id'     => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) || empty( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? '' : absint( $param );
					}
				),
				'retailer_id' => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) || empty( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? '' : absint( $param );
					}
				),
				'search'      => array(
					'default' => ''
				),
				'number'      => array(
					'default'           => 20,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'orderby'     => array(
					'default' => 'id'
				),
				'order'       => array(
					'default' => 'ASC'
				)
			)
		) );

		// Add a link.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_item' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'book_id'     => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'retailer_id' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'url'         => array(
					'required'          => true,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return esc_url_raw( $param );
					}
				)
			)
		) );

		// Update a link.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_item' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'          => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'book_id'     => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'retailer_id' => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'url'         => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return esc_url_raw( $param );
					}
				)
			)
		) );

		// Delete a link.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_item' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				)
			)
		) );

	}

	/**
	 * Get all links
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'id',
				'order'   => 'ASC',
			) );

			$links = get_book_links( $args );

			$links_data = array();

			foreach ( $links as $link ) {
				$links_data[] = $link->export_vars();
			}

			return new \WP_REST_Response( $links_data );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add link
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_item( $request ) {

		try {
			$args = array(
				'book_id'     => $request->get_param( 'book_id' ),
				'retailer_id' => $request->get_param( 'retailer_id' ),
				'url'         => $request->get_param( 'url' ),
			);

			$link_id   = add_book_link( $args );
			$book_link = get_book_link( $link_id );

			if ( empty( $book_link ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new book link from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $book_link->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update a link
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_item( $request ) {

		try {
			$whitelist = array(
				'book_id',
				'retailer_id',
				'url',
				'date_created',
				'date_modified'
			);

			$args = array();

			foreach ( $request->get_params() as $param_key => $param_value ) {
				if ( in_array( $param_key, $whitelist ) ) {
					$args[ $param_key ] = $param_value;
				}
			}

			update_book_link( $request->get_param( 'id' ), $args );
			$book_link = get_book_link( $request->get_param( 'id' ) );

			if ( empty( $book_link ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new book link from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $book_link->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete a link
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_item( $request ) {

		try {
			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A book link ID is required.', 'book-database' ), 400 );
			}

			delete_book_link( $id );

			return new \WP_REST_Response( true );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}
