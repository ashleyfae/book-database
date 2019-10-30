<?php
/**
 * Reviews Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Reviews_Schema
 *
 * @package Book_Database
 */
class Reviews_Schema extends BerlinDB\Database\Schema {

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

		// reading_log_id
		array(
			'name'       => 'reading_log_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'allow_null' => true,
			'default'    => null,
			'validate'   => '\Book_Database\BerlinDB\Sanitization\absint_allow_null'
		),

		// user_id
		array(
			'name'     => 'user_id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'sortable' => true,
			'validate' => 'absint'
		),

		// post_id
		array(
			'name'       => 'post_id',
			'type'       => 'bigint',
			'length'     => '20',
			'unsigned'   => true,
			'sortable'   => true,
			'allow_null' => true,
			'default'    => null,
			'validate'   => '\Book_Database\BerlinDB\Sanitization\absint_allow_null'
		),

		// url
		array(
			'name'     => 'url',
			'type'     => 'mediumtext',
			'default'  => '',
			'validate' => 'sanitize_text_field'
		),

		// review
		array(
			'name'     => 'review',
			'type'     => 'longtext',
			'validate' => 'wp_kses_post'
		),

		// date_written
		array(
			'name'       => 'date_written',
			'type'       => 'datetime',
			'sortable'   => true,
			'date_query' => true
		),

		// date_published
		array(
			'name'       => 'date_published',
			'type'       => 'datetime',
			'sortable'   => true,
			'date_query' => true,
			'allow_null' => true,
			'default'    => null,
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