<?php
/**
 * Book Term
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Term
 * @package Book_Database
 */
class Book_Term extends Base_Object {

	protected $taxonomy = '';

	protected $name = '';

	protected $slug = '';

	protected $description = '';

	protected $image_id = 0;

	protected $links = '';

	protected $book_count = 0;

	/**
	 * Get the taxonomy slug
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return $this->taxonomy;
	}

	/**
	 * Get the name of the term
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the term slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the description
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
		return absint( $this->image_id );
	}

	/**
	 * Get the term links
	 *
	 * @return string
	 */
	public function get_links() {
		return $this->links;
	}

	/**
	 * Get the number of books associated with this term
	 *
	 * @return int
	 */
	public function get_book_count() {
		return absint( $this->book_count );
	}

}
