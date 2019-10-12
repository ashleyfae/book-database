<?php
/**
 * Book Term Relationships Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Term_Relationships_Query
 * @package Book_Database
 */
class Book_Term_Relationships_Query extends BerlinDB\Database\Query {

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
	protected $table_schema = '\\Book_Database\\Book_Term_Relationships_Schema';

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
	protected $item_shape = '\\Book_Database\\Book_Term_Relationship';

	/**
	 * Group to cache queries and queried items to
	 *
	 * @var string
	 */
	protected $cache_group = 'book_term_relationships';

	/**
	 * Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

}