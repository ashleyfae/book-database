<?php
/**
 * Loggable.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Traits;

use ContextWP\Contracts\LogInterface;

trait Loggable
{
    /** @var LogInterface|null $logger */
    protected $logger = null;

    /**
     * Logs a message.
     *
     * @since 1.0
     *
     * @param  string  $message
     *
     * @return void
     */
    public function log(string $message): void
    {
        if ($this->logger instanceof LogInterface) {
            $this->logger->log($message);
        }
    }

    /**
     * Sets the logger.
     *
     * @internal
     * @since 1.0
     *
     * @param  LogInterface  $logger
     *
     * @return static
     */
    public function setLogger(LogInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
