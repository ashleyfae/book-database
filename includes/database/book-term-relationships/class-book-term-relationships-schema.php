<?php
/**
 * Book Term Relationships Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Term_Relationships_Schema
 *
 * @package Book_Database
 */
class Book_Term_Relationships_Schema extends BerlinDB\Database\Schema {

	/**
	 * Array of database columns
	 *
	 * @var array
	 */
	public $columns = array(

		// id
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true
		),

		// term_id
		array(
			'name'     => 'term_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true
		),

		// book_id
		array(
			'name'     => 'book_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true
		),

		// date_created
		array(
			'name'       => 'date_created',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'created'    => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// date_modified
		array(
			'name'       => 'date_modified',
			'type'       => 'datetime',
			'default'    => '0000-00-00 00:00:00',
			'modified'   => true,
			'date_query' => true,
			'sortable'   => true,
		),

	);

}