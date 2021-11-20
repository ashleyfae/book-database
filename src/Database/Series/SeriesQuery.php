<?php
/**
 * Series Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Series;

use Book_Database\BerlinDB;
use Book_Database\Models\Series;

/**
 * Class SeriesQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class SeriesQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'series';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'series';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = SeriesSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'series';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'series';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Series::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'series';

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
