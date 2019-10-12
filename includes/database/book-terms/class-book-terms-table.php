<?php
/**
 * Book Terms Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Terms_Table
 *
 * @package Book_Database
 */
class Book_Terms_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'book_terms';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910126;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array(
		'201910122' => 201910122,
		'201910123' => 201910123,
		'201910124' => 201910124,
		'201910125' => 201910125,
		'201910126' => 201910126
	);

	/**
	 * Book_Terms_Table constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set up the database schema
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			taxonomy varchar(32) NOT NULL DEFAULT '',
			name varchar(200) NOT NULL DEFAULT '',
			slug varchar(200) NOT NULL DEFAULT '',
			description longtext NOT NULL DEFAULT '',
			image_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			links longtext NOT NULL DEFAULT '',
			count bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			UNIQUE KEY id_type_name (id, taxonomy, name),
			UNIQUE KEY id_type_slug (id, taxonomy, slug),
			INDEX taxonomy (taxonomy),
			INDEX name (name)";
	}

	/**
	 * If the old `wp_bdb_book_terms_db_version` option exists, copy that value to our new version key.
	 * This will ensure new upgrades are processed on old installs.
	 */
	public function maybe_upgrade() {

		$old_key     = $this->get_db()->prefix . 'bdb_book_terms_db_version';
		$old_version = get_option( $old_key );

		if ( false !== $old_version ) {
			update_option( $this->db_version_key, get_option( $old_key ) );

			delete_option( $old_key );
		}

		return parent::maybe_upgrade();

	}

	/**
	 * Upgrade to version 201910122
	 *      - Rename `term_id` to `id` & add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910122() {

		// Drop keys involving `term_id` or `type`.
		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX id_type_name, DROP INDEX id_type_slug, DROP INDEX type" );

		if ( $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `term_id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );
		}

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910123
	 *      - Change `image` to `image_id` & add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910123() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `image` `image_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910124
	 *      - Add `unsigned` to `count`
	 *
	 * @return bool
	 */
	protected function __201910124() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY count bigint(20) UNSIGNED NOT NULL DEFAULT 0" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910125
	 *      - Add `date_created` column
	 *      - Add `date_modified` column
	 *
	 * @return bool
	 */
	protected function __201910125() {

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

	/**
	 * Upgrade to version 201910126
	 *      - Change `type` to `taxonomy`
	 *      - Add new indexes
	 *
	 * @return bool
	 */
	protected function __201910126() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `type` `taxonomy` varchar(32) NOT NULL DEFAULT ''" );

		// Add new indexes.
		if ( $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD UNIQUE KEY id_type_name (id, taxonomy, name), ADD UNIQUE KEY id_type_slug (id, taxonomy, slug), ADD INDEX taxonomy (taxonomy)" );
		}

		return $this->is_success( $result );

	}

}