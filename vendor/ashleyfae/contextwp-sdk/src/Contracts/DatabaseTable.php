<?php
/**
 * DatabaseTable.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Contracts;

interface DatabaseTable
{
    /**
     * Database table name (without the wpdb prefix).
     *
     * @return string
     */
    public function getTableName(): string;

    /**
     * Current version of the table.
     *
     * @return int
     */
    public function getVersion(): int;

    /**
     * Retrieves the current version stored in the database.
     *
     * @return int|null
     */
    public function getDbVersion(): ?int;

    /**
     * Sets the database version.
     *
     * @param  int  $version
     *
     * @return void
     */
    public function setDbVersion(int $version): void;

    /**
     * Table schema.
     *
     * @return string
     */
    public function getSchema(): string;

    /**
     * Whether this table exists.
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Creates or updates the database table.
     *
     * @return void
     */
    public function updateOrCreate(): void;

    /**
     * Drops the table.
     *
     * @return void
     */
    public function drop(): void;

    /**
     * If the database table needs upgrading (or creating).
     *
     * @return bool
     */
    public function needsUpgrade(): bool;
}
