<?php
/**
 * REST API Base Controller
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API;

use WP_REST_Controller;
use function Book_Database\user_can_edit_books;
use function Book_Database\user_can_view_books;

/**
 * Class Controller
 * @package Book_Database\REST_API
 */
abstract class Controller extends WP_REST_Controller {

	protected $namespace = 'book-database/v1';

	protected $rest_base = '';

	/**
	 * Register routes
	 */
	public function register_routes() {

	}

	/**
	 * Request the `edit_posts` capability.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function can_view( $request ) {
		return user_can_view_books();
	}

	/**
	 * Request the `manage_options` capability.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	public function can_edit( $request ) {
		return user_can_edit_books();
	}

}