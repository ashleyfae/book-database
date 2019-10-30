<?php
/**
 * Joins with the `owned_editions` table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\BerlinDB\Database\Queries;

/**
 * Class Edition
 * @package Book_Database\BerlinDB\Database\Queries
 */
class Edition extends Join {

	/**
	 * Name of the joined table key
	 *
	 * @var string
	 */
	protected $joined_table_key = 'editions';

	/**
	 * Alias to use in queries
	 *
	 * @var string
	 */
	protected $joined_table_alias = 'ed';

	/**
	 * Column name in the editions table to match against the other table.
	 *
	 * @var string
	 */
	protected $joined_table_column = 'book_id';

	/**
	 * Column whitelist
	 *
	 * @var array
	 */
	protected $columns_whitelist = array(
		'id',
		'book_id',
		'isbn',
		'format',
		'date_acquired',
		'source_id',
		'signed',
		'date_created',
		'date_modified'
	);

}