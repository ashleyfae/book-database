<?php
/**
 * Series Query
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Series_Query
 * @package Book_Database
 */
class Series_Query extends BerlinDB\Database\Query {

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
	protected $table_alias = 's';

	/**
	 * Name of class used to set up the database schema
	 *
	 * @var string
	 */
	protected $table_schema = '\\Book_Database\\Series_Schema';

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
	protected $item_shape = '\\Book_Database\\Series';

	/**
	 * Group to cache queries and queried items to
	 *
	 * @var string
	 */
	protected $cache_group = 'series';

	/**
	 * Query constructor.
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );
	}

}