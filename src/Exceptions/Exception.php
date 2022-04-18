<?php
/**
 * Exception
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Exceptions;

/**
 * Class Exception
 *
 * @package Book_Database
 * @since 1.3 Namespace changed.
 */
class Exception extends \Exception
{

    protected $error_type = '';

    /**
     * Exception constructor.
     *
     * @param  string  $error_type  Type of error. A non-translated version of the message.
     * @param  string  $message  Error message.
     * @param  int  $code_number  Error code.
     * @param  \Exception|null  $previous
     */
    public function __construct(string $error_type, string $message, int $code_number = 0, \Throwable $previous = null)
    {
        $this->error_type = $error_type;

        parent::__construct($message, $code_number, $previous);
    }

    /**
     * Get the error type
     *
     * @return string
     */
    public function get_error_type(): string
    {
        return $this->error_type;
    }

}
