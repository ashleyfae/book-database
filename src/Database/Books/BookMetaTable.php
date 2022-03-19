<?php
/**
 * Book Meta Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Books;

use Book_Database\BerlinDB;

/**
 * Class BookMetaTable
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookMetaTable extends BerlinDB\Database\Table
{

    /**
     * @var string Table name
     */
    protected $name = 'bookmeta';

    /**
     * @var int Database version in format {YYYY}{MM}{DD}{1}
     */
    protected $version = 201910271;

    /**
     * @var array Upgrades to perform
     */
    protected $upgrades = array();

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
			bdb_book_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			INDEX bdb_book_id (bdb_book_id),
			INDEX meta_key (meta_key({$max_index_length}))";
    }

}
