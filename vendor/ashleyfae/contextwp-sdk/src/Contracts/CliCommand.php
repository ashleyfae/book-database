<?php
/**
 * CliCommand.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Contracts;

interface CliCommand
{
    /**
     * Name of the command. Note that this will be prefixed with `contextwp`.
     *
     * @since 1.0
     *
     * @return string
     */
    public static function commandName(): string;

    /**
     * Runs the command.
     *
     * @since 1.0
     *
     * @param  array  $args
     * @param  array  $assocArgs
     *
     * @return void
     */
    public function __invoke(array $args, array $assocArgs): void;
}
