<?php
/**
 * Book Link Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use Book_Database\Models\Retailer;
use function Book_Database\get_retailer;

/**
 * Class BookLink
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class BookLink extends Model
{

    /**
     * @var int ID of the associated book.
     */
    protected $book_id = 0;

    /**
     * @var int ID of the retailer.
     */
    protected $retailer_id = 0;

    /**
     * @var string
     */
    protected $url = '';

    /**
     * Get the ID of the associated book.
     *
     * @return int
     */
    public function get_book_id(): int
    {
        return absint($this->book_id);
    }

    /**
     * Get the ID of the retailer
     *
     * @return int
     */
    public function get_retailer_id(): int
    {
        return absint($this->retailer_id);
    }

    /**
     * Get the URL
     *
     * @return string
     */
    public function get_url(): string
    {
        return $this->url;
    }

    /**
     * Format the book link
     *
     * @return string
     */
    public function format(): string
    {
        $retailer = get_retailer($this->get_retailer_id());

        if ($retailer instanceof Retailer) {
            $html = $retailer->build_link($this->get_url());
        } else {
            $html = make_clickable($this->get_url());
        }

        /**
         * Filters the formatted book link
         *
         * @param  string  $html  Final HTML to be displayed in the book layout.
         * @param  Retailer  $retailer  Retailer object.
         * @param  BookLink  $this  Book link object.
         */
        return apply_filters('book-database/book-link/format', $html, $retailer, $this);
    }

}
