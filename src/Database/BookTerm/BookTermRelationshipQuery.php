<?php
/**
 * Book Term Relationships Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookTerm;

use Book_Database\BerlinDB;
use Book_Database\Models\BookTermRelationship;

/**
 * Class BookTermRelationshipQuery
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTermRelationshipQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'book_term_relationships';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'btr';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = BookTermRelationshipsSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'book_term_relationship';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'book_term_relationships';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = BookTermRelationship::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'book_term_relationships';

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
