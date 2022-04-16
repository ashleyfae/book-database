<?php
/**
 * LogInterface.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Contracts;

interface LogInterface
{
    /**
     * Logs a message.
     *
     * @since 1.0
     *
     * @param  string  $message
     *
     * @return void
     */
    public function log(string $message): void;
}
