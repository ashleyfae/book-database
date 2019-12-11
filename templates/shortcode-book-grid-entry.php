<?php
/**
 * Shortcode: `[book-grid]` - Single book entry
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * @var array        $atts      Shortcode attributes.
 * @var Book         $book      Book object.
 * @var Review|false $review    False if we haven't included review data, Review object if we have.
 * @var object       $book_data Full object from the database.
 */

?>
<div id="book-<?php echo esc_attr( $book->get_id() ); ?>" class="book-grid-entry bdb-grid-entry">
	<?php
	if ( $book->get_cover_id() ) {
		echo $book->get_cover( $atts['cover-size'] );
	}

	if ( ! empty( $atts['show-ratings'] ) && ! empty( $book_data->avg_rating ) ) {
		?>
		<p class="book-grid-rating">
			<?php
			$rating = new Rating( $book_data->avg_rating );
			echo $rating->format( get_option( 'rating_display', 'html_stars' ) );
			?>
		</p>
		<?php
	}

	if ( ! empty( $atts['show-pub-date'] ) ) {
		?>
		<p class="bdb-book-pub-date">
			<?php echo esc_html( $book->get_pub_date( true ) ); ?>
		</p>
		<?php
	}

	if ( ! empty( $atts['show-review-link'] ) && $review instanceof Review && $review->get_permalink( true ) ) {
		$target = $review->is_external() ? 'blank' : 'self';
		?>
		<p class="bdb-book-review-link">
			<a href="<?php echo esc_url( $review->get_permalink() ); ?>" class="button bdb-read-review-link" target="_<?php echo esc_attr( $target ); ?>"><?php _e( 'Read Review', 'book-database' ); ?></a>
		</p>
		<?php
	}

	if ( ! empty( $atts['show-goodreads-link'] ) && $book->get_goodreads_url() ) {
		?>
		<p>
			<a href="<?php echo esc_url( $book->get_goodreads_url() ); ?>" class="button bdb-goodreads-link" target="_blank"><?php _e( 'Goodreads', 'book-database' ); ?></a>
		</p>
		<?php
	}

	if ( ! empty( $atts['show-purchase-links'] ) && $book->get_links() ) {
		foreach ( $book->get_links() as $link ) {
			?>
			<div class="bdb-grid-purchase-link">
				<?php echo $link->format(); ?>
			</div>
			<?php
		}
	}
	?>
</div>
