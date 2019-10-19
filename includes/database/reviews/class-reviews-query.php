<?php
/**
 * Reviews Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Reviews_Query
 * @package Book_Database
 */
class Reviews_Query extends BerlinDB\Database\Query {

	/**
	 * Name of the table to query
	 *
	 * @var string
	 */
	protected $table_name = 'reviews';

	/**
	 * String used to alias the database table in MySQL statements
	 *
	 * @var string
	 */
	protected $table_alias = 'rev';

	/**
	 * Name of class used to set up the database schema
	 *
	 * @var string
	 */
	protected $table_schema = '\\Book_Database\\Reviews_Schema';

	/**
	 * Name for a single item
	 *
	 * @var string
	 */
	protected $item_name = 'review';

	/**
	 * Plural version for a group of items
	 *
	 * @var string
	 */
	protected $item_name_plural = 'reviews';

	/**
	 * Class name to turn IDs into these objects
	 *
	 * @var string
	 */
	protected $item_shape = '\\Book_Database\\Review';

	/**
	 * Group to cache queries and queried items to
	 *
	 * @var string
	 */
	protected $cache_group = 'reviews';

	/**
	 * Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

	/**
	 * Get the column in this table to join with the taxonomy terms column.
	 *
	 * @return string
	 */
	protected function get_tax_query_join_column_name() {
		return 'book_id';
	}

	/**
	 * Get the column in this table to join with the book column.
	 *
	 * @return string
	 */
	protected function get_author_query_join_column_name() {
		return 'book_id';
	}

}