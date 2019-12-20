<?php
/**
 * Factory
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

/**
 * Class Factory
 *
 * @package Book_Database\Tests
 */
class Factory extends \WP_UnitTest_Factory {

	/**
	 * @var Factory\Book_Factory
	 */
	public $book;

	/**
	 * @var Factory\Series_Factory
	 */
	public $series;

	/**
	 * Factory constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->book   = new Factory\Book_Factory( $this );
		$this->series = new Factory\Series_Factory( $this );
	}

}