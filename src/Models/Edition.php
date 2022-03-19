<?php
/**
 * Edition Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use function Book_Database\format_date;
use function Book_Database\get_book_formats;
use function Book_Database\get_book_term;

/**
 * Class Edition
 *
 * @package Book_Database
 * @since 1.3 Namespace changed.
 */
class Edition extends Model
{

    protected $book_id = 0;

    protected $isbn = '';

    protected $format = '';

    protected $date_acquired = null;

    protected $source_id = null;

    protected $signed = null;

    /**
     * Get the ID of the book
     *
     * @return int
     */
    public function get_book_id(): int
    {
        return absint($this->book_id);
    }

    /**
     * Get the ISBN
     *
     * @return string
     */
    public function get_isbn(): string
    {
        return $this->isbn;
    }

    /**
     * Get the format (ebook, hardback, etc.)
     *
     * @return string
     */
    public function get_format(): string
    {
        return $this->format;
    }

    /**
     * Get the date the book was acquired
     *
     * @param  bool  $formatted  Whether or not to format the date for display.
     * @param  string  $format  Format to display the formatted date in. Default to site format.
     *
     * @return string|null
     */
    public function get_date_acquired(bool $formatted = false, string $format = ''): ?string
    {
        return (! empty($this->date_acquired) && $formatted)
            ? format_date($this->date_acquired, $format)
            : $this->date_acquired;
    }

    /**
     * Get the ID of the source term
     *
     * @return int|null
     */
    public function get_source_id(): ?int
    {
        return ! empty($this->source_id) ? absint($this->source_id) : null;
    }

    /**
     * Whether or not the book is signed
     *
     * @return bool
     */
    public function is_signed(): bool
    {
        return ! empty($this->signed);
    }

    /**
     * Export properties
     *
     * @return array
     */
    public function export_vars(): array
    {
        $vars                = parent::export_vars();
        $vars['format_name'] = get_book_formats()[$this->get_format()] ?? '';

        if ($this->get_source_id()) {
            $source = get_book_term($this->get_source_id());

            if ($source instanceof BookTerm) {
                $vars['source_name'] = $source->get_name();
            }
        }

        return $vars;
    }

}
