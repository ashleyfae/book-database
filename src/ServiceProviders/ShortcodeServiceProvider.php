<?php
/**
 * ShortcodeServiceProvider.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database\ServiceProviders;

use Book_Database\Shortcodes\BookGridShortcode;
use Book_Database\Shortcodes\BookReviewsShortcode;
use Book_Database\Shortcodes\BookShortcode;
use Book_Database\Shortcodes\Shortcode;
use function Book_Database\book_database;

class ShortcodeServiceProvider implements ServiceProvider
{
    protected $shortcodes = [
        BookGridShortcode::class,
        BookReviewsShortcode::class,
        BookShortcode::class,
    ];

    public function register(): void
    {

    }

    public function boot(): void
    {
        foreach ($this->shortcodes as $shortcode) {
            if (is_subclass_of($shortcode, Shortcode::class)) {
                /** @var Shortcode $shortcode */
                $shortcode = book_database()->make($shortcode);
                add_shortcode($shortcode::tag(), [$shortcode, 'make']);
            }
        }
    }
}
