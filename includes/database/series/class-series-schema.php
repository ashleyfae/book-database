<?php
/**
 * Series Schema Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Series_Schema
 *
 * @package Book_Database
 */
class Series_Schema extends BerlinDB\Database\Schema {

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

		// name
		array(
			'name'       => 'name',
			'type'       => 'varchar',
			'length'     => '200',
			'sortable'   => true,
			'searchable' => true
		),

		// slug
		array(
			'name'       => 'slug',
			'type'       => 'varchar',
			'length'     => '200',
			'sortable'   => true,
			'searchable' => true,
			'validate'   => 'sanitize_key'
		),

		// description
		array(
			'name' => 'description',
			'type' => 'longtext',
		),

		// number_books
		array(
			'name'     => 'number_books',
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