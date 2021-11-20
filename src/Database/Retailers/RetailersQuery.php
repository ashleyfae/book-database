<?php
/**
 * Retailers Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Retailers;

use Book_Database\BerlinDB;
use Book_Database\Retailer;

/**
 * Class RetailersQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class RetailersQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'retailers';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'retailer';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = RetailersSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'retailer';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'retailers';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Retailer::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'retailers';

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
