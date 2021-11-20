<?php
/**
 * Book Author Relationships Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookAuthor;

use Book_Database\BerlinDB;

/**
 * Class BookAuthorSchema
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookAuthorSchema extends BerlinDB\Database\Schema
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

        // author_id
        array(
            'name'     => 'author_id',
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
            'default'    => '',
            'created'    => true,
            'date_query' => true,
            'sortable'   => true,
        ),

        // date_modified
        array(
            'name'       => 'date_modified',
            'type'       => 'datetime',
            'default'    => '',
            'modified'   => true,
            'date_query' => true,
            'sortable'   => true,
        ),

    );

}
