<?php
/**
 * Base Object
 *
 * Extended by core objects.
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use Book_Database\Traits\HasQueryInterface;
use function Book_Database\format_date;

/**
 * Class Model
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
abstract class Model
{
    use HasQueryInterface;

    public $id = 0;

    public $date_created = '';

    public $date_modified = '';

    /**
     * Object constructor
     *
     * @param  array|object  $args  Object to populate vars for.
     */
    public function __construct($args = array())
    {
        $this->set_vars($args);
    }

    /**
     * Set class properties from arguments
     *
     * @param  array  $args
     */
    protected function set_vars($args = array())
    {
        // Bail if empty.
        if (empty($args)) {
            return;
        }

        // Cast to an array.
        if (! is_array($args)) {
            $args = (array) $args;
        }

        foreach ($args as $key => $value) {
            if (! property_exists($this, $key)) {
                continue;
            }

            if ('0000-00-00 00:00:00' === $value) {
                $value = null;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * Get the ID
     *
     * @return int
     */
    public function get_id(): int
    {
        return absint($this->id);
    }

    /**
     * Get the created date
     *
     * @param  bool  $formatted  Whether or not to format the result for display.
     * @param  string  $format  Format to display in. Defaults to site format.
     *
     * @return string
     */
    public function get_date_created(bool $formatted, string $format = ''): string
    {
        return (! empty($this->date_created) && $formatted)
            ? format_date($this->date_created, $format)
            : $this->date_created;
    }

    /**
     * Get the modified date
     *
     * @param  bool  $formatted  Whether or not to format the result for display.
     * @param  string  $format  Format to display in. Defaults to site format.
     *
     * @return string
     */
    public function get_date_modified(bool $formatted = false, string $format = ''): string
    {
        return (! empty($this->date_modified) && $formatted)
            ? format_date($this->date_modified, $format)
            : $this->date_modified;
    }

    /**
     * Get all object properties as an array
     *
     * @return array
     */
    public function export_vars(): array
    {
        return get_object_vars($this);
    }

}
