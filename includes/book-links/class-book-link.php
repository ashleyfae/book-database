<?php
/**
 * Book Link Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Link
 * @package Book_Database
 */
class Book_Link extends Base_Object {

	/**
	 * @var int ID of the associated book.
	 */
	protected $book_id = 0;

	/**
	 * @var int ID of the retailer.
	 */
	protected $retailer_id = 0;

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * Get the ID of the associated book.
	 *
	 * @return int
	 */
	public function get_book_id() {
		return absint( $this->book_id );
	}

	/**
	 * Get the ID of the retailer
	 *
	 * @return int
	 */
	public function get_retailer_id() {
		return absint( $this->retailer_id );
	}

	/**
	 * Get the URL
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

}