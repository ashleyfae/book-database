<?php
/**
 * Table.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Database\Tables;

use Ashleyfae\WPDB\DB;
use ContextWP\Contracts\DatabaseTable;

abstract class Table implements DatabaseTable
{
    /**
     * @inheritDoc
     */
    public function exists(): bool
    {
        return (bool) DB::get_var(
            DB::prepare(
                "SHOW TABLES LIKE %s",
                DB::esc_like(DB::applyPrefix($this->getTableName()))
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreate(): void
    {
        if (file_exists(ABSPATH.'wp-admin/includes/upgrade.php')) {
            require_once ABSPATH.'wp-admin/includes/upgrade.php';
        }

        $tableName = DB::applyPrefix($this->getTableName());
        $charset   = DB::getInstance()->charset;
        $collate   = DB::getInstance()->collate;

        DB::delta(
            "CREATE TABLE {$tableName} ({$this->getSchema()}) DEFAULT CHARACTER SET {$charset} COLLATE {$collate};"
        );

        $this->setDbVersion($this->getVersion());
    }

    /**
     * @inheritDoc
     */
    public function drop(): void
    {
        $tableName = DB::applyPrefix($this->getTableName());

        DB::query(
            "DROP TABLE IF EXISTS {$tableName}"
        );
    }

    /**
     * Retrieves the option_name for where we store the database version.
     *
     * @return string
     */
    protected function getVersionOptionName(): string
    {
        return $this->getTableName().'_db_version';
    }

    /**
     * @inheritDoc
     */
    public function getDbVersion(): ?int
    {
        $version = get_option($this->getVersionOptionName());

        return ! empty($version) ? (int) $version : null;
    }

    /**
     * @inheritDoc
     */
    public function setDbVersion(int $version): void
    {
        update_option($this->getVersionOptionName(), $version);
    }

    /**
     * @inheritDoc
     */
    public function needsUpgrade(): bool
    {
        $dbVersion = $this->getDbVersion();

        return empty($dbVersion) || version_compare($dbVersion, $this->getVersion(), '<');
    }
}
