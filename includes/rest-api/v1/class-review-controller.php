<?php
/**
 * Review Controller
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\Rating;
use Book_Database\REST_API\Controller;
use function Book_Database\add_review;
use function Book_Database\delete_review;
use function Book_Database\get_book;
use function Book_Database\get_review;
use function Book_Database\get_reviews;
use function Book_Database\update_review;

/**
 * Class Review
 * @package Book_Database\REST_API\v1
 */
class Review extends Controller {

	protected $rest_base = 'review';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all reviews. (this is /reviews)
		register_rest_route( $this->namespace, $this->rest_base . 's', array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_reviews' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'number'        => array(
					'default'           => 20,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'orderby'       => array(
					'default' => 'review.date_written'
				),
				'order'         => array(
					'default' => 'ASC'
				),
				'rating_format' => array()
			)
		) );

		// Add a new review.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_review' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'book_id'        => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$book = get_book( $param );

						return ! empty( $book );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'user_id'        => array(
					'required'          => true,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'post_id'        => array(
					'default'           => null,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? null : absint( $param );
					}
				),
				'url'            => array(
					'default'           => null,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? null : esc_url_raw( $param );
					}
				),
				'review'         => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return wp_kses_post( $param );
					}
				),
				'date_written'   => array(
					'default'           => date( 'Y-m-d H:i:s' ),
					'sanitize_callback' => function ( $param, $request, $key ) {
						return date( 'Y-m-d H:i:s', strtotime( $param ) );
					}
				),
				'date_published' => array(
					'default'           => null,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? null : date( 'Y-m-d H:i:s', strtotime( $param ) );
					}
				),
			)
		) );

		// Update an existing review.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_review' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$review = get_review( $param );

						return ! empty( $review );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				)
			)
		) );

		// Delete a review.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_review' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id' => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						$review = get_review( $param );

						return ! empty( $review );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				)
			)
		) );

	}

	/**
	 * Get all reviews
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_reviews( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'review.date_written',
				'order'   => 'ASC'
			) );

			$reviews = get_reviews( $args );

			// Format ratings.
			if ( $request->get_param( 'rating_format' ) ) {
				foreach ( $reviews as $key => $review ) {
					if ( is_null( $review->rating ) ) {
						continue;
					}

					$rating                            = new Rating( $review->rating );
					$reviews[ $key ]->rating_formatted = $rating->format( $request->get_param( 'rating_format' ) );
					$reviews[ $key ]->rating           = $rating->round_rating();
				}
			}

			return new \WP_REST_Response( $reviews );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add a new review
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_review( $request ) {

		try {

			$review_id = add_review( $request->get_params() );
			$review    = get_review( $review_id );

			if ( empty( $review ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new review from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $review->export_vars() );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update an existing review
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_review( $request ) {

		try {

			update_review( $request->get_param( 'id' ), $request->get_params() );

			$review = get_review( $request->get_param( 'id' ) );

			return new \WP_REST_Response( $review->export_vars() );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete a review
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_review( $request ) {

		try {

			delete_review( $request->get_param( 'id' ) );

			return new \WP_REST_Response( true );

		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}