<?php
/**
 * Series Schema Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Series;

use Book_Database\BerlinDB;

/**
 * Class SeriesSchema
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class SeriesSchema extends BerlinDB\Database\Schema
{

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
            'searchable' => true,
            'validate'   => 'sanitize_text_field'
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
            'name'     => 'description',
            'type'     => 'longtext',
            'validate' => 'wp_kses_post'
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
            'default'    => '', // True default is current time, set in query class
            'created'    => true,
            'date_query' => true,
            'sortable'   => true,
        ),

        // date_modified
        array(
            'name'       => 'date_modified',
            'type'       => 'datetime',
            'default'    => '', // True default is current time, set in query class
            'modified'   => true,
            'date_query' => true,
            'sortable'   => true,
        ),

    );

}
