<?php
/**
 * Book Taxonomy Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

/**
 * Class BookTaxonomy
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTaxonomy extends Model
{

    protected $name = '';

    protected $slug = '';

    protected $format = 'text';

    /**
     * Get the name
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Get the slug
     *
     * @return string
     */
    public function get_slug(): string
    {
        return $this->slug;
    }

    /**
     * Get the format - either `text` or `checkbox`
     *
     * @return string
     */
    public function get_format(): string
    {
        return $this->format;
    }

}
