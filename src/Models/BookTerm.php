<?php
/**
 * Book Term
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use Book_Database\Models\Model;
use function absint;

/**
 * Class BookTerm
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTerm extends Model
{

    protected $taxonomy = '';

    protected $name = '';

    protected $slug = '';

    protected $description = '';

    protected $image_id = 0;

    protected $links = '';

    protected $book_count = 0;

    /**
     * Get the taxonomy slug
     *
     * @return string
     */
    public function get_taxonomy(): string
    {
        return $this->taxonomy;
    }

    /**
     * Get the name of the term
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Get the term slug
     *
     * @return string
     */
    public function get_slug(): string
    {
        return $this->slug;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * Get the ID of the image attachment
     *
     * @return int
     */
    public function get_image_id(): int
    {
        return absint($this->image_id);
    }

    /**
     * Get the term links
     *
     * @return string
     */
    public function get_links(): array
    {
        return $this->links;
    }

    /**
     * Get the number of books associated with this term
     *
     * @return int
     */
    public function get_book_count(): int
    {
        return absint($this->book_count);
    }

}
