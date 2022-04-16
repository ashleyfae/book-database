<?php
/**
 * DB.php
 *
 * @package   wp-db
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\WPDB;

use Ashleyfae\WPDB\Exceptions\DatabaseQueryException;

/**
 * Static wrapper for `\wpdb`.
 * Taken from GiveWP.
 *
 * @method static int|bool query(string $query)
 * @method static int|false insert(string $table, array $data, array|string $format)
 * @method static int|false delete(string $table, array $where, array|string $where_format)
 * @method static int|false update(string $table, array $data, array $where, array|string $format, array|string $where_format)
 * @method static int|false replace(string $table, array $data, array|string $format)
 * @method static null|string get_var(string $query = null, int $x = 0, int $y = 0)
 * @method static array|object|null|void get_row(string $query = null, string $output = OBJECT, int $y = 0)
 * @method static array get_col(string $query = null, int $x = 0)
 * @method static array|object|null get_results(string $query = null, string $output = OBJECT)
 * @method static string get_charset_collate()
 * @method static string esc_like(string $text)
 */
class DB
{
    /**
     * Returns an instance of wpdb.
     *
     * @return \wpdb
     */
    public static function getInstance(): \wpdb
    {
        global $wpdb;

        return $wpdb;
    }

    /**
     * Runs the dbDelta function.
     *
     * @see dbDelta()
     *
     * @param  string  $delta
     *
     * @return array
     * @throws DatabaseQueryException
     */
    public static function delta(string $delta): array
    {
        return self::runQueryWithErrorChecking(
            function () use ($delta) {
                return dbDelta($delta);
            }
        );
    }

    /**
     * Prepares the query.
     *
     * @param $query
     * @param ...$args
     *
     * @return string|void
     */
    public static function prepare($query, ...$args)
    {
        return static::getInstance()->prepare($query, ...$args);
    }

    /**
     * Wrapper to statically run DB methods.
     *
     * @since 1.0
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws DatabaseQueryException
     */
    public static function __callStatic($name, $arguments)
    {
        return self::runQueryWithErrorChecking(
            function () use ($name, $arguments) {
                return call_user_func_array([static::getInstance(), $name], $arguments);
            }
        );
    }

    /**
     * Get last insert ID
     *
     * @since 1.0
     * @return int
     */
    public static function lastInsertId(): int
    {
        return (int) static::getInstance()->insert_id;
    }

    /**
     * Applies the table prefix to a given table name.
     *
     * @param  string  $tableName
     *
     * @return string
     */
    public static function applyPrefix(string $tableName): string
    {
        return static::getInstance()->prefix.$tableName;
    }

    /**
     * Runs a query callable and checks to see if any unique SQL errors occurred when it was run
     *
     * @since 1.0
     *
     * @param  Callable  $queryCaller
     *
     * @return mixed
     * @throws DatabaseQueryException
     */
    private static function runQueryWithErrorChecking(callable $queryCaller)
    {
        global $EZSQL_ERROR;
        require_once ABSPATH.'wp-admin/includes/upgrade.php';

        $errorCount    = is_array($EZSQL_ERROR) ? count($EZSQL_ERROR) : 0;
        $hasShowErrors = static::getInstance()->hide_errors();

        $output = $queryCaller();

        if ($hasShowErrors) {
            static::getInstance()->show_errors();
        }

        $wpError = self::getQueryErrors($errorCount);

        if (! empty($wpError->errors)) {
            throw new DatabaseQueryException($wpError->get_error_message());
        }

        return $output;
    }


    /**
     * Retrieves the SQL errors stored by WordPress
     *
     * @since 1.0
     *
     * @param  int  $initialCount
     *
     * @return \WP_Error
     */
    private static function getQueryErrors(int $initialCount = 0): \WP_Error
    {
        global $EZSQL_ERROR;

        $wpError = new \WP_Error();

        if (is_array($EZSQL_ERROR)) {
            for ($index = $initialCount, $indexMax = count($EZSQL_ERROR); $index < $indexMax; $index++) {
                $error = $EZSQL_ERROR[$index];

                if (
                    empty($error['error_str']) ||
                    empty($error['query']) ||
                    strpos($error['query'], 'DESCRIBE ') === 0
                ) {
                    continue;
                }

                $wpError->add('db_delta_error', $error['error_str']);
            }
        }

        return $wpError;
    }

}
