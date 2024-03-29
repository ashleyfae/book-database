<?php
/**
 * Books Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\Books;

use Book_Database\BerlinDB;

/**
 * Class BooksSchema
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BooksSchema extends BerlinDB\Database\Schema
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

        // cover_id
        array(
            'name'       => 'cover_id',
            'type'       => 'bigint',
            'length'     => '20',
            'unsigned'   => true,
            'sortable'   => true,
            'allow_null' => true,
            'default'    => null,
            'validate'   => '\Book_Database\BerlinDB\Sanitization\absint_allow_null'
        ),

        // title
        array(
            'name'       => 'title',
            'type'       => 'text',
            'sortable'   => true,
            'searchable' => true,
            'default'    => '',
            'validate'   => 'sanitize_text_field'
        ),

        // index_title
        array(
            'name'       => 'index_title',
            'type'       => 'text',
            'sortable'   => true,
            'searchable' => true,
            'default'    => '',
            'validate'   => 'sanitize_text_field'
        ),

        // series_id
        array(
            'name'       => 'series_id',
            'type'       => 'bigint',
            'length'     => '20',
            'unsigned'   => true,
            'sortable'   => true,
            'allow_null' => true,
            'default'    => null,
            'validate'   => '\Book_Database\BerlinDB\Sanitization\absint_allow_null'
        ),

        // series_position
        array(
            'name'       => 'series_position',
            'type'       => 'float',
            'sortable'   => true,
            'allow_null' => true,
            'default'    => null,
            'validate'   => '\Book_Database\BerlinDB\Sanitization\floatval_int_allow_null'
        ),

        // pub_date
        array(
            'name'       => 'pub_date',
            'type'       => 'date',
            'sortable'   => true,
            'date_query' => true,
            'validate'   => '\Book_Database\BerlinDB\Sanitization\validate_date'
        ),

        // pages
        array(
            'name'       => 'pages',
            'type'       => 'bigint',
            'length'     => '20',
            'unsigned'   => true,
            'sortable'   => true,
            'allow_null' => true,
            'default'    => null,
            'validate'   => '\Book_Database\BerlinDB\Sanitization\absint_allow_null'
        ),

        // synopsis
        array(
            'name'     => 'synopsis',
            'type'     => 'longtext',
            'validate' => 'wp_kses_post'
        ),

        // goodreads_url
        array(
            'name'     => 'goodreads_url',
            'type'     => 'text',
            'validate' => 'sanitize_text_field'
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
