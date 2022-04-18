<?php
/**
 * BookGrid.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database\Shortcodes;

use Book_Database\Book_Grid_Query;
use Book_Database\Models\Book;
use Book_Database\Models\Review;
use function Book_Database\get_book_taxonomies;
use function Book_Database\get_book_template_part;

class BookGridShortcode implements Shortcode
{

    public static function tag(): string
    {
        return 'book-grid';
    }

    public function make($atts, $content = ''): string
    {
        $default_atts = array(
            'ids'                 => '',
            'author'              => '',
            'series'              => '',
            'rating'              => '',
            'pub-date-after'      => '',
            'pub-date-before'     => '',
            'pub-year'            => '',
            'read-status'         => '',
            'review-date-after'   => '',
            'review-date-before'  => '',
            'review-start-date'   => '', // Deprecated
            'review-end-date'     => '', // Deprecated
            'reviews-only'        => false,
            'show-ratings'        => false,
            'show-pub-date'       => true,
            'show-goodreads-link' => false,
            'show-purchase-links' => false,
            'show-review-link'    => false,
            'orderby'             => 'book.id',
            'order'               => 'DESC',
            'cover-size'          => 'large',
            'per-page'            => 20,
        );

        foreach (get_book_taxonomies(array('fields' => 'slug')) as $tax_slug) {
            $default_atts[$tax_slug] = '';
        }

        $atts = shortcode_atts($default_atts, $atts, 'book-grid');

        // Validate booleans.
        $booleans = array(
            'reviews-only', 'show-ratings', 'show-pub-date', 'show-goodreads-link', 'show-purchase-links',
            'show-review-link'
        );
        foreach ($booleans as $boolean) {
            $atts[$boolean] = filter_var($atts[$boolean], FILTER_VALIDATE_BOOLEAN);
        }

        // Replace "review-start-date" and "review-end-date".
        if (! empty($atts['review-start-date'])) {
            $atts['review-date-after'] = $atts['review-start-date'];
        }
        if (! empty($atts['review-end-date'])) {
            $atts['review-date-before'] = $atts['review-end-date'];
        }

        $query = new Book_Grid_Query($atts);

        ob_start();
        ?>
        <div id="bdb-books">
            <?php
            $books    = $query->get_results();
            $template = get_book_template_part('shortcode-book-grid-entry', '', false);

            if (! empty($books)) {
                echo '<div class="bdb-book-list-number-results bdb-book-grid-number-results">'.sprintf(_n('%s book found',
                        '%s books found', $query->total_results, 'book-database'), $query->total_results).'</div>';
                echo '<div class="bdb-book-list bdb-book-grid">';
                foreach ($books as $book_data) {
                    $book   = new Book($book_data);
                    $review = false;

                    // Create a review object if we can.
                    if (! empty($book_data->review_id)) {
                        $review_data = array(
                            'id'             => $book_data->review_id ?? 0,
                            'book_id'        => $book->get_id(),
                            'reading_log_id' => $book_data->review_reading_log_id ?? null,
                            'user_id'        => $book_data->review_user_id ?? null,
                            'post_id'        => $book_data->review_post_id ?? null,
                            'url'            => $book_data->review_url ?? '',
                            'review'         => $book_data->review_review ?? '',
                            'date_written'   => $book_data->review_date_written ?? '',
                            'date_published' => $book_data->review_date_published ?? null,
                            'date_created'   => $book_data->review_date_created ?? '',
                            'date_modified'  => $book_data->review_date_modified ?? ''
                        );

                        $review = new Review($review_data);
                    }

                    include $template;
                }
                echo '</div>';
            } else {
                ?>
                <p><?php _e('No books found.', 'book-database'); ?></p>
                <?php
            }

            if ($query->total_results > count($books)) {
                ?>
                <nav class="bdb-pagination bdb-book-grid-pagination pagination">
                    <?php echo $query->get_pagination(); ?>
                </nav>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
