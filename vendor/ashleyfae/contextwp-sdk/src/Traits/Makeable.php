<?php
/**
 * Makeable.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Traits;

trait Makeable
{
    /**
     * Makes a new instance of the class.
     *
     * @return static
     */
    public static function make()
    {
        return new static;
    }
}
