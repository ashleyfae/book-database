<?php
/**
 * Component.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Contracts;

interface Component
{
    /**
     * Loads the component.
     *
     * @return void
     */
    public function load(): void;
}
