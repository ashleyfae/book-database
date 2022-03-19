<?php
/**
 * Book Term Relationship Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

/**
 * Class BookTermRelationship
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookTermRelationship extends Model
{

    protected $term_id = 0;

    protected $book_id = 0;

    /**
     * Get the term ID
     *
     * @return int
     */
    public function get_term_id(): int
    {
        return absint($this->term_id);
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
