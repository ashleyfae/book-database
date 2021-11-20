<?php
/**
 * Review Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

use Book_Database\Database\Reviews\ReviewsQuery;
use Book_Database\Exceptions\Exception;
use Book_Database\Models\Review;

/**
 * Get a single review by its ID
 *
 * @param  int  $review_id
 *
 * @return Review|false
 */
function get_review($review_id)
{
    try {
        return Review::find($review_id);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Get a single review by a column name/value combo
 *
 * @param  string  $column_name
 * @param  mixed  $column_value
 *
 * @return Review|false
 */
function get_review_by(string $column_name, $column_value)
{
    try {
        return Review::findBy($column_name, $column_value);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Query for reviews
 *
 * @param array $args                       {
 *                                          Query arguments to override the defaults.
 *
 * @type array  $author_query               Filter based on author fields/values.
 * @type array  $book_query                 Filter based on book fields/values.
 * @type array  $series_query               Filter based on series fields/values.
 * @type array  $reading_log_query          Filter based on reading log fields/values.
 * @type array  $review_query               Filter based on review fields/values.
 * @type array  $edition_query              Filter based on edition fields/values.
 * @type array  $tax_query                  Filter based on taxonomy fields/values.
 * @type string $orderby                    Field to order by. Must contain table alias prefix. Default `review.id`.
 * @type string $order                      How to order the results.
 * @type int    $number                     Number of results.
 * @type int    $offset                     Offset the results.
 * @type bool   $count                      Whether or not to only return a count. Default false.
 * }
 *
 * @return object[] Array of database objects.
 */
function get_reviews(array $args = []): array
{
    try {
        return Review::query($args);
    } catch (\Exception $e) {
        return [];
    }
}

/**
 * Count the reviews
 *
 * @param array $args
 *
 * @see get_reviews() for accepted arguments.
 *
 * @return int
 */
function count_reviews(array $args = []): int
{
    try {
        return Review::count($args);
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * Add a new review
 *
 * @param array      $args           {
 *
 * @type int         $book_id        Required. ID of the book this review is of.
 * @type int|null    $post_id        Optional. ID of the post where the review is written.
 * @type int         $user_id        Optional. ID of the user who wrote the review. Default to current user ID.
 * @type string      $url            Optional. External URL for where the review is published.
 * @type string      $review         Optional. Review contents.
 * @type string      $date_written   Optional. Date the review was written in MySQL format / UTC. Default to now.
 * @type string|null $date_published Optional. Date the review was (or will be) published in MySQL format / UTC>
 * }
 *
 * @return int ID of the newly created review.
 * @throws Exception
 */
function add_review(array $args = []): int
{
    return Review::create($args);
}

/**
 * Update an existing review
 *
 * @param int   $review_id ID of the review to update.
 * @param array $args      Arguments to update.
 *
 * @return bool
 * @throws Exception
 */
function update_review($review_id, array $args = []): bool
{
    return Review::update($review_id, $args);
}

/**
 * Delete a review
 *
 * @param  int  $review_id  ID of the review to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_review($review_id): bool
{
    Review::delete($review_id);

    return true;
}

/**
 * Get the reviews admin page URL.
 *
 * @param  array  $args  Query args to append to the URL.
 *
 * @return string
 */
function get_reviews_admin_page_url(array $args = []): string
{
    $sanitized_args = array();

    foreach ($args as $key => $value) {
        $sanitized_args[sanitize_key($key)] = urlencode($value);
    }

    return add_query_arg($sanitized_args, admin_url('admin.php?page=bdb-reviews'));
}

/**
 * Returns an array of distinct user IDs.
 *
 * @return array
 */
function get_reviewer_user_ids(): array
{
    global $wpdb;

    $review_table = book_database()->get_table('reviews')->get_table_name();

    return $wpdb->get_col("SELECT DISTINCT user_id FROM {$review_table}");
}

/**
 * Returns an array of all the years that reviews have been written/published in.
 *
 * @param  string  $type  Date type - either `written` or `published`.
 * @param  string  $order  Either ASC or DESC.
 *
 * @return array
 */
function get_review_years(string $type = 'written', string $order = 'DESC'): array
{
    global $wpdb;

    $review_table = book_database()->get_table('reviews')->get_table_name();
    $date_type    = 'written' === $type ? 'date_written' : 'date_published';
    $order        = 'DESC' === $order ? 'DESC' : 'ASC';

    return $wpdb->get_col("SELECT DISTINCT YEAR( {$date_type} ) FROM {$review_table} WHERE {$date_type} IS NOT NULL ORDER BY {$date_type} {$order}");
}

/**
 * Returns an array of post types that you can add reviews to
 *
 * @return array
 */
function get_review_post_types(): array
{
    return apply_filters('book-database/review-post-types', ['post']);
}
