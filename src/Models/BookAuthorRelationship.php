<?php
/**
 * Book Author Relationship Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

/**
 * Class BookAuthorRelationship
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookAuthorRelationship extends Model
{

    protected $author_id = 0;

    protected $book_id = 0;

    /**
     * Get the author ID
     *
     * @return int
     */
    public function get_author_id(): int
    {
        return absint($this->author_id);
    }

    /**
     * Get the book ID
     *
     * @return int
     */
    public function get_book_id(): int
    {
        return absint($this->book_id);
    }

}
