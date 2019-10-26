<?php
/**
 * Reviews Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Reviews_Table
 *
 * @package Book_Database
 */
class Reviews_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'reviews';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910261;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array(
		'201910181' => 201910181,
		'201910182' => 201910182,
		'201910183' => 201910183,
		'201910184' => 201910184,
		'201910185' => 201910185,
		'201910261' => 201910261
	);

	/**
	 * Reviews_Table constructor.
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
			user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			post_id bigint(20) UNSIGNED DEFAULT NULL,
			url mediumtext NOT NULL DEFAULT '',
			review longtext NOT NULL DEFAULT '',
			date_written datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_published datetime DEFAULT NULL,
			date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			INDEX book_id (book_id),
			INDEX date_written_book_id (date_written, book_id),
			INDEX date_written_id (date_written, id),
			INDEX post_id (post_id),
			INDEX user_id (user_id)";
	}

	/**
	 * If the old `wp_bdb_reviews_db_version` option exists, copy that value to our new version key.
	 * This will ensure new upgrades are processed on old installs.
	 */
	public function maybe_upgrade() {

		$old_key     = $this->get_db()->prefix . 'bdb_reviews_db_version';
		$old_version = get_option( $old_key );

		if ( false !== $old_version ) {
			update_option( $this->db_version_key, get_option( $old_key ) );

			delete_option( $old_key );
		}

		return parent::maybe_upgrade();
	}

	/**
	 * Upgrade to version 201910181
	 *      - Drop the `date_written_ID` index
	 *
	 * @return bool
	 */
	protected function __201910181() {

		if ( $this->get_db()->query( "SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'date_written_ID'" ) ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX date_written_ID" );
		} else {
			$result = true;
		}

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910182
	 *      - Rename `ID` to `id` & add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910182() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `ID` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910183
	 *      - Add a new `date_written_id` index
	 *
	 * @return bool
	 */
	protected function __201910183() {

		$result = $this->get_db()->query( "SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'date_writtenid'" );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX date_written_id (date_written, id)" );
		}

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910184
	 *      - Add `date_created` column
	 *      - Add `date_modified` column
	 *
	 * @return bool
	 */
	protected function __201910184() {

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
	 * Upgrade to version 201910185
	 *      - Change `book_id` column - add `unsigned`
	 *      - Change `post_id` column - add `unsigned` and allow null
	 *      - Change `user_id` column - add `unsigned`
	 *
	 * @return bool
	 */
	protected function __201910185() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY book_id bigint(20) UNSIGNED NOT NULL DEFAULT 0" );
		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY post_id bigint(20) UNSIGNED DEFAULT NULL" );
		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0 AFTER book_id" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910261
	 *      - Drop column `rating`
	 *
	 * @return bool
	 */
	protected function __201910261() {

		if ( $this->column_exists( 'rating' ) ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP COLUMN `rating`" );
		} else {
			$result = true;
		}

		return $this->is_success( $result );

	}

}