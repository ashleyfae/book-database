<?php
/**
 * Book Term Relationship Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Term_Relationship
 * @package Book_Database
 */
class Book_Term_Relationship extends Base_Object {

	protected $term_id = 0;

	protected $book_id = 0;

	/**
	 * Get the term ID
	 *
	 * @return int
	 */
	public function get_term_id() {
		return absint( $this->term_id );
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
