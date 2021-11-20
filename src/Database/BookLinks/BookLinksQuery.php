<?php
/**
 * Book Links Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookLinks;

use Book_Database\BerlinDB;
use Book_Database\Book_Link;

/**
 * Class BookLinksQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookLinksQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'book_links';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'link';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = BookLinksSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'book_link';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'book_links';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Book_Link::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'book_links';

    /**
     * Query constructor.
     *
     * @param  array  $args
     */
    public function __construct($args = array())
    {
        parent::__construct($args);
    }

}
