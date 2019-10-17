<?php
/**
 * Joins with the `reading_log` table.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\BerlinDB\Database\Queries;

/**
 * Class Reading_Log
 * @package Book_Database\BerlinDB\Database\Queries
 */
class Reading_Log extends Join {

	/**
	 * Name of the joined table key
	 *
	 * @var string
	 */
	protected $joined_table_key = 'reading_log';

	/**
	 * Alias to use in queries
	 *
	 * @var string
	 */
	protected $joined_table_alias = 'log';

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
		'review_id',
		'user_id',
		'date_started',
		'date_finished',
		'percentage_complete',
		'rating',
		'date_modified'
	);

}