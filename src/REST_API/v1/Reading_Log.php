<?php
/**
 * Reading Log Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API\v1;

use Book_Database\Exception;
use Book_Database\REST_API\Controller;
use function Book_Database\add_reading_log;
use function Book_Database\delete_reading_log;
use function Book_Database\get_available_ratings;
use function Book_Database\get_edition;
use function Book_Database\get_reading_log;
use function Book_Database\get_reading_logs;
use function Book_Database\update_reading_log;

/**
 * Class Reading_Log
 * @package Book_Database\REST_API\v1
 */
class Reading_Log extends Controller {

	protected $rest_base = 'reading-log';

	/**
	 * Register routes
	 */
	public function register_routes() {

		// Get all reading logs.
		register_rest_route( $this->namespace, $this->rest_base, array(
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_reading_logs' ),
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
				'user_id'       => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param ) || empty( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? '' : absint( $param );
					}
				),
				'date_started'  => array(
					'default' => '',
				),
				'date_finished' => array(
					'default' => '',
				),
				'rating'        => array(
					'default'           => '',
					'validate_callback' => function ( $param, $request, $key ) {
						return empty( $param ) || array_key_exists( (string) $param, get_available_ratings() );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
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

		// Add a reading log.
		register_rest_route( $this->namespace, $this->rest_base . '/add', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'add_reading_log' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'book_id'             => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'edition_id'          => array(
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						if ( ! is_numeric( $param ) ) {
							return false;
						}

						return get_edition( absint( $param ) ) instanceof \Book_Database\Edition;
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return ! empty( absint( $param ) ) ? absint( $param ) : null;
					}
				),
				'user_id'             => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'date_started'        => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'date_finished'       => array(
					'default'           => '',
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'percentage_complete' => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						$abs = floatval( $param );

						if ( 0 >= $abs ) {
							return 0;
						} elseif ( 1 <= $abs ) {
							return 1;
						} else {
							return round( $abs, 2 );
						}
					}
				),
				'rating'              => array(
					'default'           => null,
					'validate_callback' => function ( $param, $request, $key ) {
						return empty( $param ) || array_key_exists( (string) $param, get_available_ratings() );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? null : sanitize_text_field( $param );
					}
				)
			)
		) );

		// Update a reading log.
		register_rest_route( $this->namespace, $this->rest_base . '/update/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_reading_log' ),
			'permission_callback' => array( $this, 'can_edit' ),
			'args'                => array(
				'id'                  => array(
					'required'          => true,
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'book_id'             => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'edition_id'          => array(
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						if ( ! is_numeric( $param ) ) {
							return false;
						}

						return get_edition( absint( $param ) ) instanceof \Book_Database\Edition;
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return ! empty( absint( $param ) ) ? absint( $param ) : null;
					}
				),
				'user_id'             => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return absint( $param );
					}
				),
				'date_started'        => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'date_finished'       => array(
					'sanitize_callback' => function ( $param, $request, $key ) {
						return sanitize_text_field( $param );
					}
				),
				'percentage_complete' => array(
					'validate_callback' => function ( $param, $request, $key ) {
						if ( empty( $param ) ) {
							return true;
						}

						return is_numeric( $param );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						$abs = floatval( $param );

						if ( 0 >= $abs ) {
							return 0;
						} elseif ( 1 <= $abs ) {
							return 1;
						} else {
							return round( $abs, 2 );
						}
					}
				),
				'rating'              => array(
					'validate_callback' => function ( $param, $request, $key ) {
						return empty( $param ) || array_key_exists( (string) $param, get_available_ratings() );
					},
					'sanitize_callback' => function ( $param, $request, $key ) {
						return empty( $param ) ? null : sanitize_text_field( $param );
					}
				)
			)
		) );

		// Delete a reading log.
		register_rest_route( $this->namespace, $this->rest_base . '/delete/(?P<id>\d+)', array(
			'methods'             => \WP_REST_Server::DELETABLE,
			'callback'            => array( $this, 'delete_reading_log' ),
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
	 * Get all reading logs
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_reading_logs( $request ) {

		try {
			$args = wp_parse_args( $request->get_params(), array(
				'orderby' => 'id',
				'order'   => 'ASC',
			) );

			$logs = get_reading_logs( $args );

			$logs_data = array();

			foreach ( $logs as $log ) {
				$logs_data[] = $log->export_vars();
			}

			return new \WP_REST_Response( $logs_data );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Add reading log
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function add_reading_log( $request ) {

		try {
			$args = array(
				'book_id'             => $request->get_param( 'book_id' ),
				'edition_id'          => $request->get_param( 'edition_id' ),
				'user_id'             => $request->get_param( 'user_id' ),
				'date_started'        => $request->get_param( 'date_started' ),
				'date_finished'       => $request->get_param( 'date_finished' ),
				'percentage_complete' => $request->get_param( 'percentage_complete' ),
				'rating'              => $request->get_param( 'rating' )
			);

			$log_id      = add_reading_log( $args );
			$reading_log = get_reading_log( $log_id );

			if ( empty( $reading_log ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new reading log from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $reading_log->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Update a reading log
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function update_reading_log( $request ) {

		try {
			$whitelist = array(
				'book_id',
				'edition_id',
				'user_id',
				'date_started',
				'date_finished',
				'percentage_complete',
				'rating'
			);

			$args = array();

			foreach ( $request->get_params() as $param_key => $param_value ) {
				if ( in_array( $param_key, $whitelist ) ) {
					$args[ $param_key ] = $param_value;
				}
			}

			update_reading_log( $request->get_param( 'id' ), $args );
			$reading_log = get_reading_log( $request->get_param( 'id' ) );

			if ( empty( $reading_log ) ) {
				throw new Exception( 'database_failure', __( 'Failed to retrieve new reading log from the database.', 'book-database' ), 500 );
			}

			return new \WP_REST_Response( $reading_log->export_vars() );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

	/**
	 * Delete a reading log
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete_reading_log( $request ) {

		try {
			$id = strtolower( $request->get_param( 'id' ) );

			if ( empty( $id ) ) {
				throw new Exception( 'missing_required_parameter', __( 'A reading log ID is required.', 'book-database' ), 400 );
			}

			delete_reading_log( $id );

			return new \WP_REST_Response( true );
		} catch ( Exception $e ) {
			return new \WP_REST_Response( $e->getMessage(), $e->getCode() );
		}

	}

}
