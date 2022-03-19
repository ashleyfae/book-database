<?php
/**
 * Book Taxonomies Schema
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Database\BookTaxonomies;

use Book_Database\BerlinDB;

/**
 * Class BookTaxonomiesSchema
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTaxonomiesSchema extends BerlinDB\Database\Schema
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
            'length'     => '32',
            'sortable'   => true,
            'searchable' => true,
            'validate'   => 'sanitize_text_field'
        ),

        // slug
        array(
            'name'     => 'slug',
            'type'     => 'varchar',
            'length'   => '32',
            'sortable' => true,
            'validate' => 'sanitize_key'
        ),

        // format
        array(
            'name'     => 'format',
            'type'     => 'varchar',
            'length'   => '32',
            'sortable' => true,
            'default'  => 'text',
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
