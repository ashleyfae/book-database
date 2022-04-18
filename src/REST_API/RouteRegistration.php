<?php
/**
 * REST API Setup
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\REST_API;

use Book_Database\REST_API;

/**
 * Class REST_API
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class RouteRegistration
{

    /**
     * Register routes
     */
    public function register_routes(): void
    {
        $controllers = array(
            '\Book_Database\REST_API\v1\Analytics',
            '\Book_Database\REST_API\v1\Author',
            '\Book_Database\REST_API\v1\Book',
            '\Book_Database\REST_API\v1\Book_Link',
            '\Book_Database\REST_API\v1\Book_Term',
            '\Book_Database\REST_API\v1\Edition',
            '\Book_Database\REST_API\v1\Reading_Log',
            '\Book_Database\REST_API\v1\Retailer',
            '\Book_Database\REST_API\v1\Review',
            '\Book_Database\REST_API\v1\Series',
            '\Book_Database\REST_API\v1\Taxonomy',
            '\Book_Database\REST_API\v1\Utility',
        );

        foreach ($controllers as $controller) {
            $controller = new $controller();

            /**
             * @var REST_API\Controller $controller
             */
            $controller->register_routes();
        }
    }

}
