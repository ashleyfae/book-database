<?php
/**
 * Editions Table
 *
 * @todo      Note: `source_id` depends on their being a `source` taxonomy. Consider source table or protection against
 *            deleting the `source` taxonomy.
 * @todo      Consider `format` table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Editions_Table
 *
 * @package Book_Database
 */
class Editions_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'owned_editions';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910141;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array(
		'201910132' => 201910132,
		'201910133' => 201910133,
		'201910134' => 201910134,
		'201910135' => 201910135,
		'201910141' => 201910141
	);

	/**
	 * Owned_Editions_Table constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set up the database schema
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			book_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			isbn varchar(13) NOT NULL DEFAULT '',
			format varchar(200) NOT NULL DEFAULT '',
			date_acquired datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			source_id bigint(20) UNSIGNED DEFAULT NULL,
			signed int(1) UNSIGNED DEFAULT NULL,
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			INDEX book_id (book_id),
			INDEX isbn (isbn),
			INDEX date_acquired (dte_acquired)";
	}

	/**
	 * If the old `wp_bdb_owned_editions_db_version` option exists, copy that value to our new version key.
	 * This will ensure new upgrades are processed on old installs.
	 */
	public function maybe_upgrade() {

		$old_key     = $this->get_db()->prefix . 'bdb_owned_editions_db_version';
		$old_version = get_option( $old_key );

		if ( false !== $old_version ) {
			update_option( $this->db_version_key, get_option( $old_key ) );

			delete_option( $old_key );
		}

		return parent::maybe_upgrade();

	}

	/**
	 * Upgrade to version 201910132
	 *      - Rename `ID` to `id` & add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910132() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `ID` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910133
	 *      - Rename `source` to `source_id` & add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910133() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `source` source_id bigint(20) UNSIGNED DEFAULT NULL" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910134
	 *      - Add `date_created` column
	 *      - Add `date_modified` column
	 *
	 * @return bool
	 */
	protected function __201910134() {

		$result = $this->column_exists( 'date_created' );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00'" );
		}

		$result = $this->column_exists( 'date_modified' );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00'" );
		}

		return $result;

	}

	/**
	 * Upgrade to version 201910135
	 *      - Add `isbn` index
	 *
	 * @return bool
	 */
	protected function __201910135() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX isbn (isbn)" );

		return $result;

	}

	/**
	 * Upgrade to version 201910141
	 *      - Add `date_acquired` index
	 *
	 * @return bool
	 */
	protected function __201910141() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX date_acquired (date_acquired)" );

		return $result;

	}

}