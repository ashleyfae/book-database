<?php
/**
 * Review Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Models;

use Book_Database\Database\Reviews\ReviewsQuery;
use Book_Database\Exceptions\Exception;
use function Book_Database\book_database;
use function Book_Database\format_date;

/**
 * Class Review
 *
 * @method static ReviewsQuery getQueryInterface()
 *
 * @package Book_Database
 * @since 1.3 Namespace changed.
 */
class Review extends Model
{
    protected static $queryInterfaceClass = ReviewsQuery::class;

    /**
     * @var int ID of the associated book.
     */
    protected $book_id = 0;

    /**
     * @var int ID of the associated reading log.
     */
    protected $reading_log_id = 0;

    /**
     * @var int ID of the user who wrote the review.
     */
    protected $user_id = 0;

    /**
     * @var int|null ID of the associated blog post.
     */
    protected $post_id = null;

    /**
     * @var string External URL to the review.
     */
    protected $url = '';

    /**
     * @var string Review content.
     */
    protected $review = '';

    /**
     * @var string Date the review was written.
     */
    protected $date_written = '';

    /**
     * @var string|null Date the review was published.
     */
    protected $date_published = null;

    public static function create(array $args): int
    {
        $args = wp_parse_args($args, array(
            'book_id'        => 0,
            'reading_log_id' => null,
            'user_id'        => get_current_user_id(),
            'post_id'        => null,
            'url'            => '',
            'review'         => '',
            'date_written'   => current_time('mysql', true),
            'date_published' => null,
        ));

        if (empty($args['book_id'])) {
            throw new Exception(
                'missing_parameter',
                __('Book ID is required.', 'book-database'),
                400
            );
        }

        return parent::create($args);
    }

    public static function delete(int $id): void
    {
        parent::delete($id);

        // Delete all review meta.
        global $wpdb;
        $tbl_meta = book_database()->get_table( 'review_meta' )->get_table_name();
        $query    = $wpdb->prepare( "DELETE FROM {$tbl_meta} WHERE bdb_review_id = %d", $id );
        $wpdb->query( $query );
    }

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
     * Get the ID of the user who wrote the review
     *
     * @return int|null
     */
    public function get_reading_log_id(): ?int
    {
        return is_null($this->reading_log_id) ? null : absint($this->reading_log_id);
    }

    /**
     * Get the ID of the user who wrote the review
     *
     * @return int
     */
    public function get_user_id(): int
    {
        return absint($this->user_id);
    }

    /**
     * Get the ID of the corresponding post
     *
     * This is the post the review was published on. This will only be filled out if the
     * review was published as a blog post on this site.
     *
     * @return int|null
     */
    public function get_post_id(): ?int
    {
        return is_null($this->post_id) ? null : absint($this->post_id);
    }

    /**
     * Get the URL of the review
     *
     * This will only be filled out if the review is external.
     *
     * @return string
     */
    public function get_url(): string
    {
        return $this->url;
    }

    /**
     * Whether or not this is an external review
     *
     * Will return true if `url` is filled out.
     *
     * @return bool
     */
    public function is_external(): bool
    {
        return ! empty($this->get_url());
    }

    /**
     * Get the review text
     *
     * @return string
     */
    public function get_review(): string
    {
        return $this->review;
    }

    /**
     * Get the date the review was written
     *
     * @param  bool  $formatted  Whether or not to format the result for display.
     * @param  string  $format  Format to display in. Defaults to site format.
     *
     * @return string
     */
    public function get_date_written(bool $formatted = false, string $format = ''): string
    {
        return (! empty($this->date_written) && $formatted)
            ? format_date($this->date_written, $format)
            : $this->date_written;
    }

    /**
     * Get the date the review was (or will be) published
     *
     * @param  bool  $formatted  Whether or not to format the result for display.
     * @param  string  $format  Format to display in. Defaults to site format.
     *
     * @return string|null
     */
    public function get_date_published(bool $formatted = false, string $format = ''): ?string
    {
        return (! empty($this->date_published) && $formatted)
            ? format_date($this->date_published, $format)
            : $this->date_published;
    }

    /**
     * Get the review permalink
     *
     * Returns the URL to the external review if provided, otherwise the URL to the post where
     * the review is located. Returns false if all else fails.
     *
     * @param  bool  $use_id  Whether or not to build the URL with the post ID (ugly permalinks).
     * @param  bool  $id_appended  Whether or not to includle a "skip-to" the book ID.
     *
     * @return string|false
     */
    public function get_permalink(bool $use_id = true, bool $id_appended = true)
    {
        $url = false;

        if ($this->is_external()) {
            $url = $this->get_url();
        } elseif ($this->get_post_id() && $use_id) {
            $url = add_query_arg(array('p' => urlencode($this->get_post_id())), home_url('/'));
        } elseif ($this->get_post_id()) {
            $url = get_permalink($this->get_post_id());
        }

        if ($id_appended && ! empty($url) && ! $this->is_external()) {
            $url .= '#book-'.urlencode($this->get_book_id());
        }

        /**
         * Filters the review permalink.
         *
         * @param  string|false  $url  URL to the review.
         * @param  bool  $use_id  Whether or not to build the URL with the post ID (ugly permalinks).
         * @param  bool  $id_appended  Whether or not to includle a "skip-to" the book ID.
         * @param  Review  $this  Review object.
         */
        return apply_filters('book-database/review/get/permalink', $url, $use_id, $id_appended, $this);
    }

    /**
     * Whether or not the review has been published
     *
     * Returns `true` if the review is external, or if the review published date is in the past.
     * Returns `false` f the review is associated with a post and that post is not "published".
     *
     * @return bool
     */
    public function is_published(): bool
    {
        if ($this->is_external()) {
            return true;
        }

        if ($this->get_post_id()) {
            $post = get_post($this->get_post_id());

            if ($post instanceof \WP_Post && 'publish' !== $post->post_status) {
                return false;
            }
        }

        return ($this->get_date_published(true, 'U') <= current_time('timestamp'));
    }

    /**
     * Queries for reviews.
     *
     * @since 1.3
     *
     * @param  array  $args
     *
     * @return Review[]|array
     * @throws \Exception
     */
    public static function query(array $args = []): array
    {
        return static::getQueryInterface()->get_reviews($args);
    }

}
