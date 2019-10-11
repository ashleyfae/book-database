<?php
/**
 * Book Layout
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Layout
 * @package Book_Database
 */
class Book_Layout {

	/**
	 * @var Book
	 */
	protected $book;

	protected $fields = array();

	protected $enabled_fields = array();

	/**
	 * Book_Layout constructor.
	 *
	 * @param $book
	 */
	public function __construct( $book ) {
		$this->book           = $book;
		$this->fields         = get_book_fields();
		$this->enabled_fields = get_enabled_book_fields();
	}

}