<?php
/**
 * deprecated-functions.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database;

use Book_Database\Shortcodes\BookGridShortcode;
use Book_Database\Shortcodes\BookReviewsShortcode;
use Book_Database\Shortcodes\BookShortcode;

/**
 * Book
 *
 * Display book information.
 *
 * @param  array  $atts  Shortcode attributes.
 * @param  string  $content  Shortcode content.
 *
 * @return string
 */
function book_shortcode($atts, $content = '')
{
    _deprecated_function(__FUNCTION__, '1.3', BookShortcode::class);

    return book_database(BookShortcode::class)->make($atts, $content);
}

/**
 * Book reviews
 *
 * Displays a list of book reviews with filters allowing users to change the parameters.
 *
 * @deprecated 1.3
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @return string
 */
function book_reviews_shortcode($atts, $content = '')
{
    _deprecated_function(__FUNCTION__, '1.3', BookReviewsShortcode::class);

    return book_database(BookReviewsShortcode::class)->make($atts, $content);
}

/**
 * Book grid
 *
 * Similar to `[book-reviews]` but filtering is done via shortcode attributes instead of
 * a front-end form. It also focuses on books rather than reviews.
 *
 * @deprecated 1.3
 *
 * @param  array  $atts  Shortcode attributes.
 * @param  string  $content  Shortcode content.
 *
 * @return string
 */
function book_grid_shortcode($atts, $content = '')
{
    _deprecated_function(__FUNCTION__, '1.3', BookGridShortcode::class);

    return book_database(BookGridShortcode::class)->make($atts, $content);
}

