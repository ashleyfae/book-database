<?php
/**
 * Shortcode: Book Reviews - Single Book Entry
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var BDB_Book $book
 */
$book = $entry['book'];
/**
 * @var BDB_Review $review
 */
$review = $entry['review'];

$rating = new BDB_Rating( $review->get_rating() );
$url    = $review->is_external() ? $review->get_url() : home_url( '/?p=' . $review->get_post_id() );
?>
<div id="review-<?php echo absint( $review->ID ); ?>" class="book-review-entry">
	<?php echo $book->get_cover( apply_filters( 'book-database/shortcode/book-reviews/entry/cover-image-size', 'large' ) ); ?>

	<?php if ( $review->get_rating() ) : ?>
		<p class="book-review-rating">
			<?php echo $rating->format( 'font_awesome' ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $url ) : ?>
		<a href="<?php echo esc_url( $url ); ?>" class="btn btn-primary button bookdb-read-review-link"><?php _e( 'Read Review', 'book-database' ); ?></a>
	<?php endif; ?>
</div>