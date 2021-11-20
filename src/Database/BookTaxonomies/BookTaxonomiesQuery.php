<?php
/**
 * Book Taxonomies Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since 1.3
 */

namespace Book_Database\Database\BookTaxonomies;

use Book_Database\BerlinDB;
use Book_Database\Models\BookTaxonomy;

/**
 * Class Book_Taxonomies_Query
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTaxonomiesQuery extends BerlinDB\Database\Query
{

    /**
     * Name of the table to query
     *
     * @var string
     */
    protected $table_name = 'book_taxonomies';

    /**
     * String used to alias the database table in MySQL statements
     *
     * @var string
     */
    protected $table_alias = 'btax';

    /**
     * Name of class used to set up the database schema
     *
     * @var string
     */
    protected $table_schema = BookTaxonomiesSchema::class;

    /**
     * Name for a single item
     *
     * @var string
     */
    protected $item_name = 'book_taxonomy';

    /**
     * Plural version for a group of items
     *
     * @var string
     */
    protected $item_name_plural = 'book_taxonomies';

    /**
     * Class name to turn IDs into these objects
     *
     * @var string
     */
    protected $item_shape = BookTaxonomy::class;

    /**
     * Group to cache queries and queried items to
     *
     * @var string
     */
    protected $cache_group = 'book_taxonomies';

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
