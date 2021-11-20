<?php
/**
 * Review Meta Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Reviews;

use Book_Database\BerlinDB;

/**
 * Class ReviewMetaTable
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class ReviewMetaTable extends BerlinDB\Database\Table
{

    /**
     * @var string Table name
     */
    protected $name = 'reviewmeta';

    /**
     * @var int Database version in format {YYYY}{MM}{DD}{1}
     */
    protected $version = 201910274;

    /**
     * @var array Upgrades to perform
     */
    protected $upgrades = array(
        '201910272' => 201910272,
        '201910273' => 201910273,
        '201910274' => 201910274
    );

    /**
     * Reviews_Table constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set up the database schema
     */
    protected function set_schema()
    {
        $max_index_length = 191;
        $this->schema     = "meta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			bdb_review_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			INDEX bdb_review_id (bdb_review_id),
			INDEX meta_key (meta_key({$max_index_length}))";
    }

    /**
     * If the old `wp_bdb_reviewmeta_db_version` option exists, copy that value to our new version key.
     * This will ensure new upgrades are processed on old installs.
     */
    public function maybe_upgrade()
    {

        $old_key     = $this->get_db()->prefix.'bdb_reviewmeta_db_version';
        $old_version = get_option($old_key);

        if (false !== $old_version) {
            update_option($this->db_version_key, get_option($old_key));

            delete_option($old_key);
        }

        return parent::maybe_upgrade();
    }

    /**
     * Upgrade to version 201910272
     *      - Drop the `review_id` index
     *
     * @return bool
     */
    protected function __201910272()
    {

        if ($this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'review_id'")) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} DROP INDEX review_id");
        } else {
            $result = true;
        }

        return $this->is_success($result);

    }

    /**
     * Upgrade to version 201910273
     *      - Rename `review_id` to `bdb_review_id` & add `unsigned`
     *
     * @return bool
     */
    protected function __201910273()
    {

        if ($this->column_exists('review_id')) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} CHANGE `review_id` `bdb_review_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0");
        } else {
            $result = true;
        }

        return $this->is_success($result);

    }

    /**
     * Upgrade to version 201910274
     *      - Add a new `bdb_review_id` index
     *
     * @return bool
     */
    protected function __201910274()
    {

        $result = $this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'bdb_review_id'");

        if (! $result) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} ADD INDEX bdb_review_id (bdb_review_id)");
        }

        return $this->is_success($result);

    }

}
