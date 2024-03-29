<?php
/**
 * Reading Log Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use Book_Database\Models\Edition;
use Book_Database\Models\Review;
use function Book_Database\format_date;
use function Book_Database\get_book;
use function Book_Database\get_edition;
use function Book_Database\get_review_by;

/**
 * Class ReadingLog
 *
 * @package Book_Database
 * @since 1.3 Class renamed.
 */
class ReadingLog extends Model
{

    protected $book_id = 0;

    protected $edition_id = null;

    protected $user_id = 0;

    protected $date_started = '';

    protected $date_finished = '';

    protected $percentage_complete = 0;

    protected $rating = null;

    /**
     * Get the ID of the associated book
     *
     * @return int
     */
    public function get_book_id(): int
    {
        return absint($this->book_id);
    }

    /**
     * Get the ID of the associated edition
     *
     * @return int|null
     */
    public function get_edition_id(): ?int
    {
        return ! empty($this->edition_id) ? absint($this->edition_id) : null;
    }

    /**
     * Get the ID of the user who made this entry
     *
     * @return int
     */
    public function get_user_id(): int
    {
        return absint($this->user_id);
    }

    /**
     * Get the date the user started reading
     *
     * @param  bool  $formatted  Whether or not to format the result for display.
     * @param  string  $format  Format to display in. Defaults to site format.
     *
     * @return string
     */
    public function get_date_started(bool $formatted = false, string $format = ''): string
    {
        return (! empty($this->date_started) && $formatted)
            ? format_date($this->date_started, $format)
            : $this->date_started;
    }

    /**
     * Get the date the user finished reading
     *
     * @param  bool  $formatted  Whether or not to format the result for display.
     * @param  string  $format  Format to display in. Defaults to site format.
     *
     * @return string|null
     */
    public function get_date_finished(bool $formatted = false, string $format = ''): ?string
    {
        return (! empty($this->date_finished) && $formatted)
            ? format_date($this->date_finished, $format)
            : $this->date_finished;
    }

    /**
     * Get the percentage complete
     *
     * Note: This is the `percentage_complete` value multiplied by 100.
     *
     * @return float|int
     */
    public function get_percentage_complete()
    {
        $percentage = floatval($this->percentage_complete);

        if ($percentage >= 1) {
            $percentage = 1;
        } elseif ($percentage <= 0) {
            $percentage = 0;
        }

        return round($percentage * 100, 2);
    }

    /**
     * Whether or not the book has been fully read
     *
     * @return bool
     */
    public function is_complete(): bool
    {
        return $this->get_percentage_complete() >= 100;
    }

    /**
     * A book is "DNF" (didn't finish) if there's a finished date but the percentage is less than 100%.
     *
     * @return bool
     */
    public function is_dnf(): bool
    {
        return ! empty($this->get_date_finished()) && ! $this->is_complete();
    }

    /**
     * Get the rating
     *
     * @return float|null
     */
    public function get_rating()
    {
        return $this->rating;
    }

    /**
     * Export vars
     *
     * @return array
     */
    public function export_vars(): array
    {

        $vars = parent::export_vars();

        $vars['is_complete'] = $this->is_complete();
        $vars['is_dnf']      = $this->is_dnf();

        // Get the edition.
        $edition         = $this->get_edition_id() ? get_edition($this->get_edition_id()) : null;
        $vars['edition'] = $edition instanceof Edition ? $edition->export_vars() : null;

        // Get the review ID.
        $review = get_review_by('reading_log_id', $this->get_id());
        if ($review instanceof Review) {
            $vars['review_id'] = $review->get_id();
        }

        // Calculate the page number we're on.
        $vars['page']      = 0;
        $vars['max_pages'] = 0;
        $book              = get_book($this->get_book_id());
        if ($book instanceof Book) {
            $percentage        = $this->get_percentage_complete() / 100;
            $vars['page']      = round($percentage * $book->get_pages());
            $vars['max_pages'] = $book->get_pages();
        }

        return $vars;

    }

}
