<?php
/**
 * Editions Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Editions_Schema
 *
 * @package Book_Database
 */
class Editions_Schema extends BerlinDB\Database\Schema {

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
			'sortable' => true
		),

		// isbn
		array(
			'name'     => 'isbn',
			'type'     => 'varchar',
			'length'   => '13',
			'sortable' => true,
			'validate' => 'sanitize_text_field'
		),

		// format
		array(
			'name'     => 'format',
			'type'     => 'varchar',
			'length'   => '13',
			'validate' => 'sanitize_text_field'
		),

		// date_acquired
		array(
			'name'       => 'date_acquired',
			'type'       => 'datetime',
			'default'    => null,
			'allow_null' => true,
			'date_query' => true,
			'sortable'   => true,
		),

		// source_id
		array(
			'name'     => 'source_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true
		),

		// signed
		array(
			'name'       => 'signed',
			'type'       => 'int',
			'length'     => '1',
			'unsigned'   => true,
			'sortable'   => true,
			'allow_null' => true,
			'default'    => null,
			'validate'   => '\Book_Database\BerlinDB\Sanitization\absint_allow_null'
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