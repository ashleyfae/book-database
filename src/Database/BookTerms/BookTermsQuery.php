<?php
/**
 * Book Terms Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookTerms;

use Book_Database\BerlinDB;
use Book_Database\Models\BookTerm;

/**
 * Class Book_Terms_Query
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTermsQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'book_terms';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'bt';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = BookTermsSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'book_term';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'book_terms';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = BookTerm::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'book_terms';

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
