<?php
/**
 * Series Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Series
 * @package Book_Database
 */
class Series extends Base_Object {

	protected $name = '';

	protected $slug = '';

	protected $description = '';

	protected $number_books = 0;

	/**
	 * Get the name of the series
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the series slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get the series description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the number of books in the series
	 *
	 * Note: This is not the number of books present in the database with this series, but
	 * rather the number of books PLANNED to be in the series. So if the series is a trilogy
	 * but there's only one book in the database, this would return `3` because there are 3
	 * books planned in the series. It's the length of the series.
	 *
	 * @return int
	 */
	public function get_number_books() {
		return absint( $this->number_books );
	}

}