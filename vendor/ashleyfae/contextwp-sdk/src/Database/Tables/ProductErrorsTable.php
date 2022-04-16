<?php
/**
 * ProductErrorsTable.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Database\Tables;

class ProductErrorsTable extends Table
{
    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'contextwp_product_errors';
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): int
    {
        return strtotime('2022-04-09 12:34:00');
    }

    /**
     * @inheritDoc
     */
    public function getSchema(): string
    {
        return "
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id varchar(191) NOT NULL,
        permanently_locked tinyint(1) DEFAULT 0,
        locked_until datetime DEFAULT NULL,
        response_body text DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY product_id (product_id),
        KEY permanently_locked_locked_until (permanently_locked, locked_until)
        ";
    }
}
