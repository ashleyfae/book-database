<?php
/**
 * BookShortcode.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database\Shortcodes;

use Book_Database\Book_Layout;
use Book_Database\Models\Book;
use function Book_Database\get_book;

class BookShortcode implements Shortcode
{

    public static function tag(): string
    {
        return 'book';
    }

    public function make($atts, $content = ''): string
    {
        $atts = shortcode_atts(array(
            'id'     => 0,
            'rating' => null,
        ), $atts, 'book');

        if (empty($atts['id']) || ! is_numeric($atts['id'])) {
            return sprintf(__('Invalid book: %s', 'book-database'), $atts['id']);
        }

        $book = get_book(absint($atts['id']));

        if (! $book instanceof Book) {
            return sprintf(__('Invalid book: %s', 'book-database'), $atts['id']);
        }

        $layout = new Book_Layout($book);
        $layout->set_rating($atts['rating']);

        $html = $layout->get_html();

        /**
         * Filters the [book] shortcode HTML.
         *
         * @param  string  $html  Formatted book layout.
         * @param  Book  $book  Book object.
         * @param  array  $atts  Shortcode attributes.
         * @param  string  $content  Shortcode content.
         */
        return apply_filters('book-database/shortcodes/book/output', $html, $book, $atts, $content);
    }
}
