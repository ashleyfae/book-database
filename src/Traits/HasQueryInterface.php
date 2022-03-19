<?php
/**
 * HasQueryInterface.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database\Traits;

use Book_Database\BerlinDB\Database\Query;
use Book_Database\Exceptions\Exception;
use Book_Database\Exceptions\ModelNotFoundException;
use Book_Database\Models\Model;

trait HasQueryInterface
{
    /**
     * @var string
     */
    protected static $queryInterfaceClass;

    /**
     * Returns the query interface for this model.
     *
     * @since 1.3
     *
     * @return Query
     * @throws \Exception
     */
    protected static function getQueryInterface(): Query
    {
        if (! isset(static::$queryInterfaceClass)) {
            throw new \Exception('Query class not set for '.static::class);
        }

        if (! is_subclass_of(static::$queryInterfaceClass, Query::class)) {
            throw new \Exception(sprintf(
                'The %s class must extend %s.',
                static::$queryInterfaceClass,
                Query::class
            ));
        }

        return new static::$queryInterfaceClass;
    }

    /**
     * Creates a new model.
     *
     * @since 1.3
     *
     * @param  array  $args
     *
     * @return int
     * @throws \Exception
     */
    public static function create(array $args): int
    {
        $id = static::getQueryInterface()->add_item($args);

        if ($id) {
            return (int) $id;
        }

        throw new Exception(
            'database_error',
            __('Failed to insert new model into the database.', 'book-database'),
            500
        );
    }

    /**
     * Updates a model by its ID.
     *
     * @since 1.3
     *
     * @param  int  $id
     * @param  array  $args
     *
     * @return bool
     * @throws \Exception
     */
    public static function update(int $id, array $args): bool
    {
        return (bool) static::getQueryInterface()->update_item($id, $args);
    }

    /**
     * Deletes a model by its ID.
     *
     * @since 1.3
     *
     * @param  int  $id
     *
     * @throws \Exception
     */
    public static function delete(int $id): void
    {
        if (! static::getQueryInterface()->delete_item($id)) {
            throw new \Exception('Failed to delete model.');
        }
    }

    /**
     * Retrieves a model by its ID.
     *
     * @since 1.3
     *
     * @param  int  $id
     *
     * @return Model
     * @throws ModelNotFoundException|\Exception
     */
    public static function find(int $id): Model
    {
        $item = static::getQueryInterface()->get_item($id);

        if (! $item) {
            throw new ModelNotFoundException();
        }

        return $item;
    }

    /**
     * Retrieves a model by a given column + value.
     *
     * @since 1.3
     *
     * @param  string  $column
     * @param  mixed  $value
     *
     * @return Model
     * @throws ModelNotFoundException|\Exception
     */
    public static function findBy(string $column, $value): Model
    {
        $item = static::getQueryInterface()->get_item_by($column, $value);

        if (! $item) {
            throw new ModelNotFoundException();
        }

        return $item;
    }

    /**
     * Queries for models.
     *
     * @since 1.3
     *
     * @param  array  $args
     *
     * @return Model[]|array
     * @throws \Exception
     */
    public static function query(array $args = []): array
    {
        return static::getQueryInterface()->query($args);
    }

    /**
     * Queries for all models.
     *
     * @since 1.3
     *
     * @param  array  $args
     *
     * @return Model[]|array
     * @throws \Exception
     */
    public static function all(array $args = []): array
    {
        return static::query(wp_parse_args($args, ['number' => 9999]));
    }

    /**
     * Counts the number of models.
     *
     * @since 1.3
     *
     * @param  array  $args
     *
     * @return int
     * @throws \Exception
     */
    public static function count(array $args = []): int
    {
        $args  = wp_parse_args($args, ['count' => true]);
        $query = static::getQueryInterface();
        $query->query($args);

        return absint($query->found_items);
    }

}
