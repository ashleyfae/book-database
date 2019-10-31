<?php
/**
 * Retailer Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Retailer
 * @package Book_Database
 */
class Retailer extends Base_Object {

	/**
	 * @var string Name of the retailer
	 */
	protected $name = '';

	/**
	 * Get the name of the retailer
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

}