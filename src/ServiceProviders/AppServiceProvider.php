<?php
/**
 * AppServiceProvider.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database\ServiceProviders;

use Book_Database\Widgets\Reading_Log;
use Book_Database\Widgets\Reviews;

class AppServiceProvider implements ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        add_action('contextwp_sdk_loaded', function (\ContextWP\SDK $sdk) {
            $sdk->register(
                (new \ContextWP\ValueObjects\Product('af272e18-bea7-42fd-b531-f898fbd55b25', '75f43cf9-febb-44f2-8b02-e73a36590d6c'))
                    ->setVersion(BDB_VERSION)
            );
        });

        /**
         * Registers widgets.
         */
        add_action( 'widgets_init', function () {
            register_widget(Reading_Log::class);
            register_widget(Reviews::class);
        } );
    }
}
