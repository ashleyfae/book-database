<?php
/**
 * Reading Logs Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\ReadingLogs;

use Book_Database\BerlinDB;

/**
 * Class ReadingLogsSchema
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class ReadingLogsSchema extends BerlinDB\Database\Schema
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

        // book_id
        array(
            'name'     => 'book_id',
            'type'     => 'bigint',
            'length'   => '20',
            'unsigned' => true,
            'sortable' => true,
            'default'  => 0
        ),

        // edition_id
        array(
            'name'       => 'edition_id',
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
            'default'  => 0
        ),

        // date_started
        array(
            'name'       => 'date_started',
            'type'       => 'datetime',
            'sortable'   => true,
            'date_query' => true,
            'allow_null' => true,
            'default'    => null
        ),

        // date_finished
        array(
            'name'       => 'date_finished',
            'type'       => 'datetime',
            'sortable'   => true,
            'date_query' => true,
            'allow_null' => true,
            'default'    => null
        ),

        // percentage_complete
        array(
            'name'     => 'percentage_complete',
            'type'     => 'decimal',
            'length'   => '5,4',
            'unsigned' => true,
            'sortable' => true,
            'default'  => 0.00,
        ),

        // rating
        array(
            'name'       => 'rating',
            'type'       => 'decimal',
            'length'     => '4,2',
            'unsigned'   => true,
            'sortable'   => true,
            'allow_null' => true,
            'default'    => null
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
