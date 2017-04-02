<?php
/**
 * Shortcode: Book Reviews - Single Book Entry
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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

$rating = new BDB_Rating( $review->rating );
$url    = $review->get_permalink();
?>
<div id="review-<?php echo absint( $review->ID ); ?>" class="book-review-entry">
	<?php
	if ( $book->get_cover_id() ) {
		echo $url ? '<a href="' . esc_url( $url ) . '">' : '';
		echo $book->get_cover( $atts['image-size'] );
		echo $url ? '</a>' : '';
	}
	?>

	<?php if ( $atts['show-ratings'] && $review->get_rating() ) : ?>
		<p class="book-review-rating">
			<?php echo $rating->format( 'font_awesome' ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $atts['show-review-link'] && $url ) : ?>
		<p><a href="<?php echo esc_url( $url ); ?>" class="button bookdb-read-review-link"><?php _e( 'Read Review', 'book-database' ); ?></a></p>
	<?php endif; ?>

	<?php if ( $atts['show-goodreads-link'] && $book->get_goodreads_url() ) : ?>
		<p><a href="<?php echo esc_url( $book->get_goodreads_url() ); ?>" class="button bookdb-goodreads-link" target="_blank"><?php _e( 'Goodreads', 'book-database' ); ?></a></p>
	<?php endif; ?>
</div>