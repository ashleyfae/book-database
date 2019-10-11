<?php
/**
 * Book Taxonomy Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Taxonomy
 * @package Book_Database
 */
class Book_Taxonomy extends Base_Object {

	protected $name = '';

	protected $slug = '';

	protected $format = 'text';

	/**
	 * Get the name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the format - either `text` or `checkbox`
	 *
	 * @return string
	 */
	public function get_format() {
		return $this->format;
	}

}