<?php
/**
 * TableManager.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Database;

use ContextWP\Contracts\Component;
use ContextWP\Contracts\DatabaseTable;
use ContextWP\Database\Tables\ProductErrorsTable;

class TableManager implements Component
{
    /** @var string[] Tables to create, update, etc. */
    protected $tables = [
        ProductErrorsTable::class,
    ];

    /**
     * @inheritDoc
     */
    public function load(): void
    {
        $this->updateOrCreateTables();
    }

    /**
     * Initializes the database tables and creates/updates any that need it.
     */
    public function updateOrCreateTables(): void
    {
        foreach ($this->getTables() as $table) {
            if ($table->needsUpgrade()) {
                $table->updateOrCreate();
            }
        }
    }

    /**
     * Creates instances of the supplied table classes.
     *
     * @return DatabaseTable[]
     */
    protected function getTables(): array
    {
        return array_map(function (string $tableName) {
            return new $tableName;
        }, $this->tables);
    }
}
