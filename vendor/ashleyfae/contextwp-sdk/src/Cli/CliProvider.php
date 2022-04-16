<?php
/**
 * CliProvider.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Cli;

use ContextWP\Cli\Commands\SendCheckInsCommand;
use ContextWP\Contracts\CliCommand;
use ContextWP\Contracts\Component;
use WP_CLI;

class CliProvider implements Component
{
    /** @var string[] $commands Available CLI commands to register. */
    protected $commands = [
        SendCheckInsCommand::class,
    ];

    /**
     * Loads the component.
     *
     * @since 1.0
     */
    public function load(): void
    {
        if ($this->shouldLoad()) {
            $this->registerCommands();
        }
    }

    /**
     * Determines if the component should be loaded -- if WP-CLI is available.
     *
     * @return bool
     */
    protected function shouldLoad(): bool
    {
        return defined('WP_CLI') && WP_CLI && class_exists('WP_CLI');
    }

    /**
     * Registers our commands.
     *
     * @since 1.0
     */
    protected function registerCommands(): void
    {
        foreach ($this->commands as $command) {
            $this->registerCommand($command);
        }
    }

    /**
     * Registers a command with WP-CLI.
     *
     * @since 1.0
     *
     * @param  string|CliCommand  $commandClass
     */
    protected function registerCommand(string $commandClass): void
    {
        WP_CLI::add_command('contextwp '.$commandClass::commandName(), $commandClass);
    }
}
