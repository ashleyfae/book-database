<?php
/**
 * Book Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\add_book;
use function Book_Database\delete_book;
use function Book_Database\generate_book_index_title;
use function Book_Database\get_book;
use function Book_Database\get_books;
use function Book_Database\update_book;

/**
 * Class Book
 * @package Book_Database\REST_API\v1
 */
class Book extends Controller {

	protected $rest_base = 'book';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all books.
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_books' ),
			'permission_callback' => array( $this, 'can_view' )
		) );

		// Add a new book.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_book' ),
			'permission_callback' => array( $this, 'can_edit' )
		) );

		// Update an existing book.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_book' ),
			'permission_callback' => array( $this, 'can_edit' )
		) );

		// Delete a book.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_book' ),
			'permission_callback' => array( $this, 'can_edit' )
		) );

		// Get the `index_title` version of a title
		register_rest_route( $this->namespace, $this->rest_base . '/index-title', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_index_title' ),
			'permission_callback' => array( $this, 'can_view' )
		) );

	}

	/**
	 * Get all books
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_taxonomies( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'date_created',
				'order'   => 'ASC'
			) );

			$books = get_books( $args );

			$book_data = array();

			foreach ( $books as $book ) {
				$book_data[ $book->get_id() ] = $book->get_data();
			}

			return new \WP_REST_Response( $book_data );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add a new book
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_book( $request ) {

		try {

			$book_id = add_book( $request->get_params() );
			$book    = get_book( $book_id );

			if ( empty( $book ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new book from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $book->get_data() );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update an existing book
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_book( $request ) {

		try {

			$book_id = $request->get_param( 'id' );

			if ( empty( $book_id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
			}

			update_book( $book_id, $request->get_params() );

			$book = get_book( $book_id );

			return new \WP_REST_Response( $book->get_data() );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete a book
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_book( $request ) {

		try {

			$book_id = $request->get_param( 'id' );

			if ( empty( $book_id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A book ID is required.', 'book-database' ), 400 );
			}

			delete_book( $book_id );

			return new \WP_REST_Response( true );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Get the `index_title` version of a book title
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_index_title( $request ) {

		try {

			$title = $request->get_param( 'title' );

			if ( empty( $title ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A book title is required.', 'book-database' ), 400 );
			}

			return new \WP_REST_Response( generate_book_index_title( $title ) );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}