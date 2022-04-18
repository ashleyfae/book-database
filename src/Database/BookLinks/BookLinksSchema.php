<?php
/**
 * Book Links Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookLinks;

use Book_Database\BerlinDB;

/**
 * Class BookLinksSchema
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookLinksSchema extends BerlinDB\Database\Schema
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
            'name'       => 'url',
            'type'       => 'text',
            'searchable' => true,
            'validate'   => 'esc_url_raw'
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
