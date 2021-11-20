<?php
/**
 * Book Terms Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookTerms;

use Book_Database\BerlinDB;

/**
 * Class BookTermsTable
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTermsTable extends BerlinDB\Database\Table
{

    /**
     * @var string Table name
     */
    protected $name = 'book_terms';

    /**
     * @var int Database version in format {YYYY}{MM}{DD}{1}
     */
    protected $version = 201910181;

    /**
     * @var array Upgrades to perform
     */
    protected $upgrades = array(
        '201910122' => 201910122,
        '201910123' => 201910123,
        '201910125' => 201910125,
        '201910126' => 201910126,
        '201910181' => 201910181
    );

    /**
     * Book_Terms_Table constructor.
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
        $this->schema = "id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			taxonomy varchar(32) NOT NULL DEFAULT '',
			name varchar(200) NOT NULL DEFAULT '',
			slug varchar(200) NOT NULL DEFAULT '',
			description longtext NOT NULL DEFAULT '',
			image_id bigint(20) UNSIGNED DEFAULT NULL,
			links longtext NOT NULL DEFAULT '',
			book_count bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			INDEX id_taxonomy_name (id, taxonomy, name),
			INDEX id_taxonomy_slug (id, taxonomy, slug),
			INDEX taxonomy (taxonomy),
			INDEX name (name)";
    }

    /**
     * If the old `wp_bdb_book_terms_db_version` option exists, copy that value to our new version key.
     * This will ensure new upgrades are processed on old installs.
     */
    public function maybe_upgrade()
    {

        $old_key     = $this->get_db()->prefix.'bdb_book_terms_db_version';
        $old_version = get_option($old_key);

        if (false !== $old_version) {
            update_option($this->db_version_key, get_option($old_key));

            delete_option($old_key);
        }

        return parent::maybe_upgrade();

    }

    /**
     * Upgrade to version 201910122
     *      - Rename `term_id` to `id` & add `unsigned`
     *
     * @return bool
     */
    protected function __201910122()
    {

        $result = true;

        // Drop keys involving `term_id` or `type`.
        if ($this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'id_type_name'")) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} DROP INDEX id_type_name");
        }
        if ($this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'id_type_slug'")) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} DROP INDEX id_type_slug");
        }
        if ($this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'type'")) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} DROP INDEX type");
        }

        if ($result && $this->column_exists('term_id')) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} CHANGE `term_id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
        }

        return $this->is_success($result);

    }

    /**
     * Upgrade to version 201910123
     *      - Change `image` to `image_id` & add `unsigned`
     *
     * @return bool
     */
    protected function __201910123()
    {

        if ($this->column_exists('image')) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} CHANGE `image` `image_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0");
        } else {
            $result = true;
        }

        return $this->is_success($result);

    }

    /**
     * Upgrade to version 201910125
     *      - Add `date_created` column
     *      - Add `date_modified` column
     *
     * @return bool
     */
    protected function __201910125()
    {

        $result = $this->column_exists('date_created');

        if (! $result) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} ADD COLUMN date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
        }

        $result = $this->column_exists('date_modified');

        if (! $result) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} ADD COLUMN date_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
        }

        return $this->is_success($result);

    }

    /**
     * Upgrade to version 201910126
     *      - Change `type` to `taxonomy`
     *      - Add new indexes
     *
     * @return bool
     */
    protected function __201910126()
    {

        if ($this->column_exists('type')) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} CHANGE `type` `taxonomy` varchar(32) NOT NULL DEFAULT ''");
        } else {
            $result = true;
        }

        // Add new indexes.
        if ($result) {
            if (! $this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'id_taxonomy_name'")) {
                $result = $this->get_db()->query("ALTER TABLE {$this->table_name} ADD UNIQUE KEY id_taxonomy_name (id, taxonomy, name)");
            }
            if (! $this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'id_taxonomy_slug'")) {
                $result = $this->get_db()->query("ALTER TABLE {$this->table_name} ADD UNIQUE KEY id_taxonomy_slug (id, taxonomy, slug)");
            }
            if (! $this->get_db()->query("SHOW INDEX FROM {$this->table_name} WHERE Key_name = 'taxonomy'")) {
                $result = $this->get_db()->query("ALTER TABLE {$this->table_name} ADD KEY taxonomy (taxonomy)");
            }
        }

        return $this->is_success($result);

    }

    /**
     * Upgrade to version 201910181
     *      - Change `count` to `book_count`
     *
     * @return bool
     */
    protected function __201910181()
    {

        if ($this->column_exists('count') && ! $this->column_exists('book_count')) {
            $result = $this->get_db()->query("ALTER TABLE {$this->table_name} CHANGE `count` `book_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0");
        } else {
            $result = true;
        }

        return $this->is_success($result);

    }

}
