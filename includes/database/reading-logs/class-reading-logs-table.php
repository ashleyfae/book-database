<?php
/**
 * Reading Logs Table Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Reading_Logs_Table
 *
 * @package Book_Database
 */
class Reading_Logs_Table extends BerlinDB\Database\Table {

	/**
	 * @var string Table name
	 */
	protected $name = 'reading_log';

	/**
	 * @var int Database version in format {YYYY}{MM}{DD}{1}
	 */
	protected $version = 201910271;

	/**
	 * @var array Upgrades to perform
	 */
	protected $upgrades = array(
		'201910132' => 201910132,
		'201910133' => 201910133,
		'201910141' => 201910141,
		'201910142' => 201910142,
		'201910271' => 201910271
	);

	/**
	 * Books_Table constructor.
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
			date_started datetime DEFAULT NULL,
			date_finished datetime DEFAULT NULL,
			percentage_complete decimal(5,4) UNSIGNED NOT NULL DEFAULT 0.00,
			rating decimal(4,2) DEFAULT NULL,
			date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			INDEX book_id (book_id),
			INDEX percentage_complete (percentage_complete),
			INDEX date_finished_book_id (date_finished, book_id),
			INDEX date_finished_percentage_complete (date_finished, percentage_complete),
			INDEX date_finished_rating (date_finished, rating),
			INDEX date_started (date_started),
			INDEX rating_book_id (rating, book_id),
			INDEX review_id (review_id),
			INDEX user_id (user_id)";
	}

	/**
	 * If the old `wp_bdb_reading_list_db_version` option exists, copy that value to our new version key.
	 * This will ensure new upgrades are processed on old installs.
	 */
	public function maybe_upgrade() {

		$old_key     = $this->get_db()->prefix . 'bdb_reading_list_db_version';
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
	 *      - Add `date_modified` column
	 *
	 * @return bool
	 */
	protected function __201910133() {

		$result = $this->column_exists( 'date_modified' );

		if ( ! $result ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD COLUMN date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00'" );
		}

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910141
	 *      - Change `complete` to `percentage_complete`
	 *
	 * @return bool
	 */
	protected function __201910141() {

		if ( $this->get_db()->query( "SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'complete'" ) ) {
			$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} DROP INDEX complete" );
		} else {
			$result = true;
		}

		if ( $result ) {
			if ( ! $this->column_exists( 'percentage_complete' ) ) {
				// First change to `float`.
				$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} CHANGE `complete` `percentage_complete` float UNSIGNED NOT NULL DEFAULT 0" );

				if ( $result ) {
					// Now change to divide by 100.
					$result = $this->get_db()->query( "UPDATE {$this->table_name} SET percentage_complete = percentage_complete / 100 WHERE percentage_complete > 0" );

					// Now change to decimal.
					if ( $result ) {
						$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY `percentage_complete` decimal(5,4) UNSIGNED NOT NULL DEFAULT 0" );

						// Now add an index.
						if ( ! $this->get_db()->query( "SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'percentage_complete'" ) ) {
							$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} ADD INDEX percentage_complete (percentage_complete)" );
						}
					}
				}
			}
		}

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910142
	 *      - Remove `dnf` rating values
	 *      - Change `rating` to `decimal(4,2)`
	 *
	 * @return bool
	 */
	protected function __201910142() {

		$result = $this->get_db()->query( "UPDATE {$this->table_name} SET rating = NULL WHERE rating = 'dnf'" );

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY rating decimal(4,2) DEFAULT NULL" );

		return $this->is_success( $result );

	}

	/**
	 * Upgrade to version 201910271
	 *      - Allow null for `review_id`
	 *      - Update to change all `0` values to `null`.
	 *
	 * @return bool
	 */
	protected function __201910271() {

		$result = $this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY review_id bigint(20) UNSIGNED DEFAULT NULL" );

		if ( $this->is_success( $result ) ) {
			$result = $this->get_db()->query( "UPDATE {$this->table_name} SET review_id = NULL WHERE review_id = 0" );
		}

		return $this->is_success( $result );

	}

}