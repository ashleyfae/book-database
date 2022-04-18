<?php
/**
 * Book Edition Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exceptions\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\add_book_term;
use function Book_Database\add_edition;
use function Book_Database\delete_edition;
use function Book_Database\get_book_formats;
use function Book_Database\get_book_term;
use function Book_Database\get_book_term_by_name_and_taxonomy;
use function Book_Database\get_book_terms;
use function Book_Database\get_edition;
use function Book_Database\get_editions;
use function Book_Database\update_edition;

/**
 * Class Edition
 * @package Book_Database\REST_API\v1
 */
class Edition extends Controller {

	protected $rest_base = 'edition';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all editions.
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_editions' ),
			'permission_callback' => array( $this, 'can_view' ),
			'args'                => array(
				'book_id'       => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) || empty( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? '' : absint( $param );
					}
				),
				'isbn'          => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'format'        => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'date_acquired' => array(
					'default' => '',
				),
				'source_id'     => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) || empty( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? '' : absint( $param );
					}
				),
				'search'        => array(
					'default' => ''
				),
				'number'        => array(
					'default'           => 20,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'orderby'       => array(
					'default' => 'id'
				),
				'order'         => array(
					'default' => 'ASC'
				)
			)
		) );

		// Add an edition.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_edition' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'book_id'       => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'isbn'          => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( wp_strip_all_tags( $param ) );
					}
				),
				'format'        => array(
					'default'           => '',
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						return array_key_exists( strtolower( $param ), get_book_formats() );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( wp_strip_all_tags( strtolower( $param ) ) );
					}
				),
				'date_acquired' => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return ! empty( $param ) ? sanitize_text_field( $param ) : null;
					}
				),
				'source_id'     => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						if ( is_numeric( $param ) ) {
							return get_book_term( $param ) instanceof \Book_Database\Models\BookTerm;
						}

						return true;
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return null;
						}

						if ( is_numeric( $param ) ) {
							return absint( $param );
						}

						try {
							// Convert source name to ID.
							$source = get_book_term_by_name_and_taxonomy( $param, 'source' );

							if ( empty( $source ) ) {
								// Add a new source.
								$source_id = add_book_term( array(
									'taxonomy' => 'source',
									'name'     => sanitize_text_field( $param )
								) );

								$source = get_book_term( $source_id );
							}

							if ( ! isset( $source ) || ! $source instanceof \Book_Database\Models\BookTerm ) {
								throw new \Exception();
							}

							return absint( $source->get_id() );
						} catch ( \Exception $e ) {
							return null;
						}
					}
				),
				'signed'        => array(
					'default'           => null,
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? null : 1;
					}
				)
			)
		) );

		// Update an edition.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_edition' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'            => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'book_id'       => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'isbn'          => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( wp_strip_all_tags( $param ) );
					}
				),
				'format'        => array(
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						return array_key_exists( strtolower( $param ), get_book_formats() );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( wp_strip_all_tags( strtolower( $param ) ) );
					}
				),
				'date_acquired' => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return ! empty( $param ) ? sanitize_text_field( $param ) : null;
					}
				),
				'source_id'     => array(
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						if ( is_numeric( $param ) ) {
							return get_book_term( $param ) instanceof \Book_Database\Models\BookTerm;
						}

						return true;
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return null;
						}

						if ( is_numeric( $param ) ) {
							return absint( $param );
						}

						try {
							// Convert source name to ID.
							$source = get_book_term_by_name_and_taxonomy( $param, 'source' );

							if ( empty( $source ) ) {
								// Add a new source.
								$source_id = add_book_term( array(
									'taxonomy' => 'source',
									'name'     => sanitize_text_field( $source )
								) );

								$source = get_book_term( $source_id );
							}

							if ( ! isset( $source ) || ! $source instanceof \Book_Database\Models\BookTerm ) {
								throw new \Exception();
							}

							return absint( $source->get_id() );
						} catch ( \Exception $e ) {
							return null;
						}
					}
				),
				'signed'        => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) || 'false' === $param ? null : 1;
					}
				)
			)
		) );

		// Delete an edition.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_edition' ),
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
	 * Get all editions
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_editions( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'id',
				'order'   => 'ASC',
			) );

			$editions = get_editions( $args );

			$editions_data = array();

			foreach ( $editions as $edition ) {
				$editions_data[] = $edition->export_vars();
			}

			return new \WP_REST_Response( $editions_data );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add edition
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_edition( $request ) {

		try {
			$args = array(
				'book_id'       => $request->get_param( 'book_id' ),
				'isbn'          => $request->get_param( 'isbn' ),
				'format'        => $request->get_param( 'format' ),
				'date_acquired' => $request->get_param( 'date_acquired' ),
				'source_id'     => $request->get_param( 'source_id' ),
				'signed'        => $request->get_param( 'signed' )
			);

			$edition_id = add_edition( $args );
			$edition    = get_edition( $edition_id );

			if ( empty( $edition ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new edition from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $edition->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update an edition
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_edition( $request ) {

		try {
			$whitelist = array(
				'book_id',
				'isbn',
				'format',
				'date_acquired',
				'source_id',
				'signed'
			);

			$args = array();

			foreach ( $request->get_params() as $param_key => $param_value ) {
				if ( in_array( $param_key, $whitelist ) ) {
					$args[ $param_key ] = $param_value;
				}
			}

			update_edition( $request->get_param( 'id' ), $args );
			$edition = get_edition( $request->get_param( 'id' ) );

			if ( empty( $edition ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new edition from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $edition->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete an edition
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_edition( $request ) {

		try {
			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'An edition ID is required.', 'book-database' ), 400 );
			}

			delete_edition( $id );

			return new \WP_REST_Response( true );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}
