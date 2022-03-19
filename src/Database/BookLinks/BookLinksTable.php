<?php
/**
 * Book Links Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookLinks;

use Book_Database\BerlinDB;

/**
 * Class BookLinksTable
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookLinksTable extends BerlinDB\Database\Table
{

    /**
     * @var string Table name
     */
    protected $name = 'book_links';

    /**
     * @var int Database version in format {YYYY}{MM}{DD}{1}
     */
    protected $version = 201910311;

    /**
     * @var array Upgrades to perform
     */
    protected $upgrades = array();

    /**
     * Book_Taxonomies_Table constructor.
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
			book_id bigint(20) UNSIGNED NOT NULL,
			retailer_id bigint(20) UNSIGNED NOT NULL,
			url text NOT NULL DEFAULT '',
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			INDEX book_id (book_id)";
    }

}
