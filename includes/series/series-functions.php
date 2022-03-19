<?php
/**
 * Series Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/*
 * Note: there is no singular `get_book_series_by()` function because both the singular
 * and plural forms are the same, so that's confusing. `get_book_series_by()` is reserved
 * for the plural version. To get a single series by ID, use `get_book_series_by( 'id', $id )`
 */

use Book_Database\Exceptions\Exception;
use Book_Database\Models\Series;

/**
 * Get a single series by a column name/value combo
 *
 * @param  string  $column_name
 * @param  mixed  $column_value
 *
 * @return Series|false
 */
function get_book_series_by(string $column_name, $column_value)
{
    try {
        return Series::findBy($column_name, $column_value);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Query for series
 *
 * @param array       $args                {
 *                                         Query arguments to override the defaults.
 *
 * @type int          $id                  An item ID to only return that item. Default empty.
 * @type array        $id__in              An array of item IDs to include. Default empty.
 * @type array        $id__not_in          An array of item IDs to exclude. Default empty.
 * @type string       $name                Filter by name. Default empty.
 * @type string       $slug                Filter by slug. Default empty.
 * @type int          $number_books        Filter by number of books in the series. Default empty.
 * @type array        $date_created_query  Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_modified_query Date query clauses to limit by. See WP_Date_Query. Default null.
 * @type array        $date_query          Query all datetime columns together. See WP_Date_Query.
 * @type bool         $count               Whether to return an item count (true) or array of objects. Default false.
 * @type string       $fields              Item fields to return. Accepts any column known names  or empty
 *                                         (returns an array of complete item objects). Default empty.
 * @type int          $number              Limit number of items to retrieve. Default 20.
 * @type int          $offset              Number of items to offset the query. Used to build LIMIT clause. Default 0.
 * @type bool         $no_found_rows       Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
 * @type string|array $orderby             Accepts 'id', 'name', 'slug', 'number_books', 'date_created', and
 *                                         'date_modified'. Also accepts false, an empty array, or 'none'
 *                                          to disable `ORDER BY` clause. Default 'id'.
 * @type string       $order               How to order results. Accepts 'ASC', 'DESC'. Default 'DESC'.
 * @type string       $search              Search term(s) to retrieve matching items for. Default empty.
 * @type bool         $update_cache        Whether to prime the cache for found items. Default false.
 * }
 *
 * @return Series[] Array of Series objects.
 */
function get_book_series(array $args = []): array
{
    try {
        return Series::query($args);
    } catch (\Exception $e) {
        return [];
    }
}

/**
 * Count the series
 *
 * @param  array  $args
 *
 * @see get_book_series() for accepted arguments.
 *
 * @return int
 */
function count_book_series(array $args = []): int
{
    try {
        return Series::count($args);
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * Add a new series
 *
 * @param  array  $args  {
 *
 * @type string $name Name of the series.
 * @type string $slug Series slug. Omit to auto generate.
 * @type string $description Description of the series.
 * @type int $number_books Number of books planned for the series.
 * }
 *
 * @return int ID of the newly created taxonomy.
 * @throws Exception|\Exception
 */
function add_book_series(array $args): int
{
    return Series::create($args);
}

/**
 * Update an existing series
 *
 * @param  int  $series_id  ID of the series to update.
 * @param  array  $args  Arguments to change.
 *
 * @return bool
 * @throws Exception
 */
function update_book_series(int $series_id, array $args = []): bool
{
    return Series::update($series_id, $args);
}

/**
 * Delete a series
 *
 * This also updates the records of each book in this series to wipe their series_id and series_position.
 *
 * @param  int  $series_id  ID of the book to delete.
 *
 * @return bool
 * @throws Exception
 */
function delete_book_series(int $series_id): bool
{
    Series::delete($series_id);

    return true;
}

/**
 * Get the series admin page URL.
 *
 * @param  array  $args  Query args to append to the URL.
 *
 * @return string
 */
function get_series_admin_page_url(array $args = []): string
{
    $sanitized_args = array();

    foreach ($args as $key => $value) {
        $sanitized_args[sanitize_key($key)] = urlencode($value);
    }

    return add_query_arg($sanitized_args, admin_url('admin.php?page=bdb-series'));
}
