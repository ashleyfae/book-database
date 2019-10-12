<?php
/**
 * REST API Setup
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class REST_API
 * @package Book_Database
 */
class REST_API {

	/**
	 * REST_API constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ), 20 );
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		$controllers = array(
			'\Book_Database\REST_API\v1\Book',
			'\Book_Database\REST_API\v1\Taxonomy',
		);

		foreach ( $controllers as $controller ) {
			$controller = new $controller();

			/**
			 * @var REST_API\Controller $controller
			 */

			$controller->register_routes();
		}
	}

}