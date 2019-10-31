<?php
/**
 * Book Links Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Book_Links_Schema
 *
 * @package Book_Database
 */
class Book_Links_Schema extends BerlinDB\Database\Schema {

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

		// book_id
		array(
			'name'     => 'book_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true,
			'validate' => 'absint'
		),

		// retailer_id
		array(
			'name'     => 'retailer_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true,
			'validate' => 'absint'
		),

		// url
		array(
			'name'       => 'name',
			'type'       => 'text',
			'searchable' => true,
			'validate'   => 'esc_url_raw'
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