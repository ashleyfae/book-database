<?php
/**
 * Exception
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Exception
 * @package Book_Database
 */
class Exception extends \Exception {

	protected $error_type = '';

	/**
	 * Exception constructor.
	 *
	 * @param string          $error_type Type of error. A non-translated version of the message.
	 * @param string          $message    Error message.
	 * @param int             $code_number Error code.
	 * @param \Exception|null $previous
	 */
	public function __construct( $error_type, $message, $code_number = 0, \Exception $previous = null ) {

		$this->error_type = $error_type;

		parent::__construct( $message, $code_number, $previous );

	}

	/**
	 * Get the error type
	 *
	 * @return string
	 */
	public function get_error_type() {
		return $this->error_type;
	}

}
