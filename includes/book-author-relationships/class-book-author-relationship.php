<?php
/**
 * Book Author Relationship Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Author_Relationship
 * @package Book_Database
 */
class Book_Author_Relationship extends Base_Object {

	protected $author_id = 0;

	protected $book_id = 0;

	/**
	 * Get the author ID
	 *
	 * @return int
	 */
	public function get_author_id() {
		return absint( $this->author_id );
	}

	/**
	 * Get the book ID
	 *
	 * @return int
	 */
	public function get_book_id() {
		return absint( $this->book_id );
	}

}