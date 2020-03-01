<?php
/**
 * Book Term Relationships Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Term_Relationships_Table
 *
 * @package Book_Database
 */
class Book_Term_Relationships_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'book_term_relationships';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910124;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array(
		'201910122' => 201910122,
		'201910123' => 201910123,
		'201910124' => 201910124
	);

	/**
	 * Book_Term_Relationships_Table constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set up the database schema
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			term_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			book_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			INDEX book_id (book_id),
			INDEX term_id (term_id)";
	}

	/**
	 * If the old `wp_bdb_book_term_relationships_db_version` option exists, copy that value to our new version key.
	 * This will ensure new upgrades are processed on old installs.
	 */
	public function maybe_upgrade() {

		$old_key     = $this->get_db()->prefix . 'bdb_book_term_relationships_db_version';
		$old_version = get_option( $old_key );

		if ( false !== $old_version ) {
			update_option( $this->db_version_key, get_option( $old_key ) );

			delete_option( $old_key );
		}

		return parent::maybe_upgrade();

	}

	/**
	 * Upgrade to version 201910122
	 *      - Rename `ID` to `id` & add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910122() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `ID` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910123
	 *      - Add `unsigned` to `term_id` and `book_id`
	 *
	 * @return bool
	 */
	protected function __201910123() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY term_id bigint(20) UNSIGNED NOT NULL DEFAULT 0" );
		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY book_id bigint(20) UNSIGNED NOT NULL DEFAULT 0" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910124
	 *      - Add `date_created` column
	 *      - Add `date_modified` column
	 *
	 * @return bool
	 */
	protected function __201910124() {

		$result = $this->column_exists( 'date_created' );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00'" );
		}

		$result = $this->column_exists( 'date_modified' );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00'" );
		}

		return $this->is_success( $result );

	}

}