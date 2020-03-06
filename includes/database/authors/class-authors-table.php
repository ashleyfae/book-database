<?php
/**
 * Authors Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Authors_Table
 *
 * @package Book_Database
 */
class Authors_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'authors';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910131;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array();

	/**
	 * Book_Taxonomies_Table constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set up the database schema
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name varchar(200) NOT NULL DEFAULT '',
			slug varchar(200) NOT NULL DEFAULT '',
			description longtext NOT NULL DEFAULT '',
			image_id bigint(20) UNSIGNED DEFAULT NULL,
			links longtext NOT NULL DEFAULT '',
			book_count bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			INDEX name (name),
			INDEX slug (slug)";
	}

}