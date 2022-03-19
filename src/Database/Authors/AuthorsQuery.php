<?php
/**
 * Authors Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Authors;

use Book_Database\Models\Author;
use Book_Database\BerlinDB;

/**
 * Class AuthorsQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class AuthorsQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'authors';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'author';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = AuthorsSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'author';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'authors';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Author::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'authors';

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
