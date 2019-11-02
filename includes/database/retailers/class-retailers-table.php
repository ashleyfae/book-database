<?php
/**
 * Retailers Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Retailers_Table
 *
 * @package Book_Database
 */
class Retailers_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'retailers';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201911021;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array(
		'201911021' => 201911021
	);

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
			template text NOT NULL DEFAULT '',
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			INDEX name (name)";
	}

	/**
	 * Upgrade to version 201911021
	 *      - add `template` column
	 *
	 * @return bool
	 */
	protected function __201911021() {

		$result = $this->column_exists( 'template' );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN template text NOT NULL DEFAULT '' AFTER `name`" );
		}

		return $this->is_success( $result );

	}

}