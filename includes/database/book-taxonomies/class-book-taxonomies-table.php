<?php
/**
 * Book Taxonomies Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Taxonomies_Table
 *
 * @package Book_Database
 */
class Book_Taxonomies_Table extends \BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'book_taxonomies';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910111;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array();

	/**
	 * Clients_Table constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set up the database schema
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name varchar(32) NOT NULL DEFAULT '',
			slug varchar(32) NOT NULL DEFAULT '',
			format varchar(32) NOT NULL DEFAULT 'text',
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			INDEX slug( slug )";
	}

}