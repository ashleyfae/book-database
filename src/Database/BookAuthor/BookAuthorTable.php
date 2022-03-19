<?php
/**
 * Book Author Relationships Table
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookAuthor;

use Book_Database\BerlinDB;

/**
 * Class BookAuthorTable
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookAuthorTable extends BerlinDB\Database\Table
{

    /**
     * @var string Table name
     */
    protected $name = 'book_author_relationships';

    /**
     * @var int Database version in format {YYYY}{MM}{DD}{1}
     */
    protected $version = 201910131;

    /**
     * @var array Upgrades to perform
     */
    protected $upgrades = array();

    /**
     * Book_Term_Relationships_Table constructor.
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
			author_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			book_id bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date_created datetime NOT NULL,
			date_modified datetime NOT NULL,
			INDEX author_id (author_id),
			INDEX book_id (book_id)";
    }

}
