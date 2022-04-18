<?php
/**
 * Book Taxonomies Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookTaxonomies;

use Book_Database\BerlinDB;

/**
 * Class BookTaxonomiesTable
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTaxonomiesTable extends BerlinDB\Database\Table
{

    /**
     * @var string Table name
     */
    protected $name = 'book_taxonomies';

    /**
     * @var int Database version in format {YYYY}{MM}{DD}{1}
     */
    protected $version = 201910111;

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
			name varchar(32) NOT NULL DEFAULT '',
			slug varchar(32) NOT NULL DEFAULT '',
			format varchar(32) NOT NULL DEFAULT 'text',
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			INDEX slug( slug )";
    }

}
