<?php
/**
 * Taxonomy Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use \Book_Database\REST_API\Controller;
use \Book_Database\Exception;
use function Book_Database\add_book_taxonomy;
use function Book_Database\bdb_update_option;
use function Book_Database\delete_book_taxonomy;
use function Book_Database\get_book_taxonomies;
use function Book_Database\get_book_taxonomy;
use function Book_Database\update_book_taxonomy;

/**
 * Class Taxonomy
 * @package Book_Database\REST_API\v1
 */
class Taxonomy extends Controller {

	protected $rest_base = 'taxonomy';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all taxonomies.
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_taxonomies' ),
			'permission_callback' => array( $this, 'can_view' )
		) );

		// Add a new taxonomy.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_taxonomy' ),
			'permission_callback' => array( $this, 'can_edit' )
		) );

		// Update an existing taxonomy.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_taxonomy' ),
			'permission_callback' => array( $this, 'can_edit' )
		) );

		// Delete a taxonomy.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_taxonomy' ),
			'permission_callback' => array( $this, 'can_edit' )
		) );

	}

	/**
	 * Get all book taxonomies
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_taxonomies( $request ) {

		try {
			$taxonomies = get_book_taxonomies( array(
				'format'  => $request->get_param( 'format' ) ?? '',
				'number'  => $request->get_param( 'number' ) ?? 20,
				'offset'  => $request->get_param( 'offset' ) ?? 0,
				'orderby' => $request->get_param( 'orderby' ) ?? 'date_created',
				'order'   => $request->get_param( 'order' ) ?? 'ASC',
				'search'  => $request->get_param( 'search' ) ?? '',
				'fields'  => array(
					'id',
					'name',
					'slug',
					'format'
				)
			) );

			return new \WP_REST_Response( $taxonomies );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add a new taxonomy
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_taxonomy( $request ) {

		try {

			$args = array(
				'name'   => $request->get_param( 'name' ) ?? '',
				'slug'   => $request->get_param( 'slug' ) ?? '',
				'format' => $request->get_param( 'format' ) ?? 'text'
			);

			$taxonomy_id = add_book_taxonomy( $args );
			$taxonomy    = get_book_taxonomy( $taxonomy_id );

			if ( empty( $taxonomy ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new taxonomy from the database.', 'book-database' ), 500 );
			}

			$taxonomy_fields = array(
				'id'            => $taxonomy->get_id(),
				'name'          => $taxonomy->get_name(),
				'slug'          => $taxonomy->get_slug(),
				'format'        => $taxonomy->get_format(),
				'date_created'  => $taxonomy->get_date_created(),
				'date_modified' => $taxonomy->get_date_modified()
			);

			return new \WP_REST_Response( $taxonomy_fields );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update an existing taxonomy
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_taxonomy( $request ) {

		try {

			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A taxonomy ID is required.', 'book-database' ), 400 );
			}

			$args = array();

			foreach ( array( 'name', 'format' ) as $param ) {

				if ( $request->get_param( $param ) ) {
					$args[ $param ] = $request->get_param( $param );
				}

			}

			update_book_taxonomy( $id, $args );

			$taxonomy = get_book_taxonomy( $id );

			$taxonomy_fields = array(
				'id'            => $taxonomy->get_id(),
				'name'          => $taxonomy->get_name(),
				'slug'          => $taxonomy->get_slug(),
				'format'        => $taxonomy->get_format(),
				'date_created'  => $taxonomy->get_date_created(),
				'date_modified' => $taxonomy->get_date_modified()
			);

			return new \WP_REST_Response( $taxonomy_fields );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update an existing taxonomy
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_taxonomy( $request ) {

		try {

			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A taxonomy ID is required.', 'book-database' ), 400 );
			}

			delete_book_taxonomy( $id );

			return new \WP_REST_Response( true );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}