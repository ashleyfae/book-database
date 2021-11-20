<?php
/**
 * Book Author Relationships Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookAuthor;

use Book_Database\BerlinDB;
use Book_Database\Book_Author_Relationship;

/**
 * Class Book_Author_Relationships_Query
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookAuthorQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'book_author_relationships';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'ar';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = BookAuthorSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'book_author_relationship';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'book_author_relationships';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = Book_Author_Relationship::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'book_author_relationships';

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
