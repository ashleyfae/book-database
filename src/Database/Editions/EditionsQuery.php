<?php
/**
 * Editions Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Editions;

use Book_Database\BerlinDB;
use Book_Database\Edition;

/**
 * Class EditionsQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class EditionsQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'owned_editions';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'ed';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = EditionsSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'edition';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'editions';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Edition::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'editions';

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
