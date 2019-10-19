<?php
/**
 * Joins with the `books` table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\BerlinDB\Database\Queries;

/**
 * Class Book
 * @package Book_Database\BerlinDB\Database\Queries
 */
class Book extends Join {

	/**
	 * Name of the joined table key
	 *
	 * @var string
	 */
	protected $joined_table_key = 'books';

	/**
	 * Alias to use in queries
	 *
	 * @var string
	 */
	protected $joined_table_alias = 'book';

	/**
	 * Column name in the books table to match against the other table.
	 *
	 * @var string
	 */
	protected $joined_table_column = 'id';

	/**
	 * Column whitelist
	 *
	 * @var array
	 */
	protected $columns_whitelist = array(
		'id',
		'cover_id',
		'title',
		'index_title',
		'series_id',
		'series_position',
		'pub_date',
		'pages',
		'synopsis',
		'goodreads_url',
		'buy_link',
		'date_created',
		'date_modified'
	);

}