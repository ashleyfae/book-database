<?php
/**
 * Series Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use Book_Database\Database\Series\SeriesQuery;
use Book_Database\Exceptions\Exception;
use Book_Database\Exceptions\ModelNotFoundException;
use function Book_Database\book_database;
use function Book_Database\count_books;
use function Book_Database\get_books;
use function Book_Database\unique_book_slug;
use function Book_Database\update_book;

/**
 * Class Series
 *
 * @package Book_Database
 * @since 1.3 Namespace changed.
 */
class Series extends Model
{
    protected static $queryInterfaceClass = SeriesQuery::class;

    protected $name = '';

    protected $slug = '';

    protected $description = '';

    protected $number_books = 0;

    public static function create(array $args): int
    {
        $args = wp_parse_args($args, array(
            'name'         => '',
            'slug'         => '',
            'description'  => '',
            'number_books' => 1,
        ));

        if (empty($args['name'])) {
            throw new Exception('missing_required_parameter', __('A taxonomy name is required.', 'book-database'), 400);
        }

        // Generate a slug.
        $args['slug'] = ! empty($args['slug'])
            ? unique_book_slug($args['slug'], 'series')
            : unique_book_slug(sanitize_title($args['name']), 'series');

        // Sanitize.
        $args['slug'] = sanitize_key($args['slug']);

        return parent::create($args);
    }

    public static function update(int $id, array $args): bool
    {
        try {
            /** @var Series $series */
            $series = static::find($id);
        } catch (ModelNotFoundException $e) {
            throw new Exception('invalid_id', __('Invalid series ID.', 'book-database'), 400);
        }

        // If the slug is changing, let's re-generate it.
        if (isset($args['slug']) && $args['slug'] !== $series->get_slug()) {
            $args['slug'] = unique_book_slug($args['slug'], 'series');
        }

        return parent::update($id, $args);
    }

    public static function delete(int $id): void
    {
        parent::delete($id);

        // Get all the books in this series.
        $books = get_books(array(
            'series_id' => absint($id),
            'number'    => 9999,
            'fields'    => 'id'
        ));

        if ($books) {
            /**
             * @var int[] $books
             */
            // Remove series_id and series_position from each book.
            foreach ($books as $book_id) {
                update_book($book_id, array(
                    'series_id'       => 0,
                    'series_position' => null
                ));
            }
        }
    }

    /**
     * Get the name of the series
     *
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * Get the series slug
     *
     * @return string
     */
    public function get_slug(): string
    {
        return $this->slug;
    }

    /**
     * Get the series description
     *
     * @return string
     */
    public function get_description(): string
    {
        return $this->description;
    }

    /**
     * Get the number of books in the series
     *
     * Note: This is not the number of books present in the database with this series, but
     * rather the number of books PLANNED to be in the series. So if the series is a trilogy
     * but there's only one book in the database, this would return `3` because there are 3
     * books planned in the series. It's the length of the series.
     *
     * @return int
     */
    public function get_number_books(): int
    {
        if (empty($this->number_books)) {
            $this->number_books = count_books(array('series_id' => $this->get_id()));
        }

        return absint($this->number_books);
    }

    /**
     * Get the number of books in this series that have been read
     *
     * @return int
     */
    public function get_number_books_read(): int
    {
        global $wpdb;

        $log_table  = book_database()->get_table('reading_log')->get_table_name();
        $book_table = book_database()->get_table('books')->get_table_name();

        $query       = $wpdb->prepare(
            "SELECT COUNT( DISTINCT book_id )
				FROM {$log_table} log
			    INNER JOIN {$book_table} b ON log.book_id = b.id
			WHERE series_id = %d
			  AND date_finished IS NOT NULL
			  AND user_id = %d",
            $this->get_id(),
            get_current_user_id()
        );
        $number_read = $wpdb->get_var($query);

        return absint($number_read);
    }

    /**
     * Get the books in this series
     *
     * @param  array  $args
     *
     * @return Book[]
     */
    public function get_books_in_series(array $args = array()): array
    {
        $args = wp_parse_args($args, array(
            'series_id' => $this->get_id(),
            'number'    => 50,
            'orderby'   => 'series_position',
            'order'     => 'ASC'
        ));

        return get_books($args);
    }

    /**
     * Get the average rating of all books in this series
     */
    public function get_average_rating(): float
    {
        global $wpdb;

        $log_table  = book_database()->get_table('reading_log')->get_table_name();
        $book_table = book_database()->get_table('books')->get_table_name();

        $query = $wpdb->prepare(
            "SELECT ROUND( AVG( rating ), 2 ) FROM {$log_table} log INNER JOIN {$book_table} b ON log.book_id = b.id WHERE series_id = %d AND rating IS NOT NULL",
            $this->get_id()
        );

        return (float) $wpdb->get_var($query);
    }

}
