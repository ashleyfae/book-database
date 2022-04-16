<?php
/**
 * Singleton.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Traits;

trait Singleton
{
    /** @var static|null $instance single instance of the class */
    protected static $instance;

    /**
     * Returns a single instance of the class.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (! isset(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }
}
