<?php
/**
 * SendCheckInsCommand.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Cli\Commands;

use ContextWP\Actions\SendCheckIns;
use ContextWP\Contracts\CliCommand;
use ContextWP\Contracts\LogInterface;
use Exception;
use WP_CLI;

class SendCheckInsCommand implements CliCommand, LogInterface
{
    /** @var SendCheckIns $sendCheckIns */
    protected $sendCheckIns;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->sendCheckIns = new SendCheckIns();
    }

    /**
     * @inheritDoc
     */
    public static function commandName(): string
    {
        return 'checkin';
    }

    public function __invoke(array $args, array $assocArgs): void
    {
        try {
            $this->sendCheckIns->setLogger($this)->withoutFiltering()->execute();
        } catch (Exception $e) {
            WP_CLI::error(sprintf('%s: %s', get_class($e), $e->getMessage()));
        }
    }

    /**
     * @inheritDoc
     */
    public function log(string $message): void
    {
        WP_CLI::log($message);
    }
}
