<?php
/**
 * Hooks.php
 *
 * Taken from GiveWP.
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Helpers;

use function Book_Database\book_database;

class Hooks
{

    /**
     * Wrapper for `add_action()`. This prevents the need to instantiate the class before
     * adding it to the hook.
     *
     * @since 1.3
     *
     * @param  string  $tag
     * @param  string  $class
     * @param  string  $method
     * @param  int  $priority
     * @param  int  $acceptedArgs
     */
    public static function addAction(
        string $tag,
        string $class,
        string $method = '__invoke',
        int $priority = 10,
        int $acceptedArgs = 1
    ) {
        if (! method_exists($class, $method)) {
            throw new \InvalidArgumentException("The method $method does not exist on $class.");
        }

        add_action(
            $tag,
            static function () use ($tag, $class, $method) {
                $instance = book_database($class);

                call_user_func_array([$instance, $method], func_get_args());
            },
            $priority,
            $acceptedArgs
        );
    }

    /**
     * Wrapper for `add_filter()`. This prevents the need to instantiate the class before
     * adding it to the hook.
     *
     * @since 3.7
     *
     * @param  string  $tag
     * @param  string  $class
     * @param  string  $method
     * @param  int  $priority
     * @param  int  $acceptedArgs
     */
    public static function addFilter(
        string $tag,
        string $class,
        string $method = '__invoke',
        int $priority = 10,
        int $acceptedArgs = 1
    ) {
        if (! method_exists($class, $method)) {
            throw new \InvalidArgumentException("The method $method does not exist on $class.");
        }

        add_filter(
            $tag,
            static function () use ($tag, $class, $method) {
                $instance = book_database($class);

                return call_user_func_array([$instance, $method], func_get_args());
            },
            $priority,
            $acceptedArgs
        );
    }

}
