<?php
/**
 * Author Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Author
 * @package Book_Database
 */
class Author extends Base_Object {

	protected $name = '';

	protected $slug = '';

	protected $description = '';

	protected $image_id = null;

	protected $links = array();

	protected $book_count = 0;

	/**
	 * Get the author's name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the author slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the author description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the ID of the image attachment
	 *
	 * @return int
	 */
	public function get_image_id() {
		return ! empty( $this->image_id ) ? absint( $this->image_id ) : 0;
	}

	/**
	 * Get an array of author links
	 *
	 * @return array()
	 */
	public function get_links() {
		return $this->links;
	}

	/**
	 * Get the number of books by this author
	 *
	 * @return int
	 */
	public function get_book_count() {
		return absint( $this->book_count );
	}

}