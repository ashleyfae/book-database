<?php
/**
 * Reading Logs Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\ReadingLogs;

use Book_Database\BerlinDB;
use Book_Database\Reading_Log;

/**
 * Class ReadingLogsQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class ReadingLogsQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'reading_log';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'log';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = ReadingLogsSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'reading_log';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'reading_logs';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Reading_Log::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'reading_logs';

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
