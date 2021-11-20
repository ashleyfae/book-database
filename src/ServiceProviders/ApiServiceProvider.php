<?php
/**
 * ApiServiceProvider.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\ServiceProviders;

use Book_Database\Helpers\Hooks;
use Book_Database\REST_API\RouteRegistration;

class ApiServiceProvider implements ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        Hooks::addAction('rest_api_init', RouteRegistration::class, 'register_routes');
    }
}
