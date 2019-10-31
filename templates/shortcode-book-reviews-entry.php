<?php
/**
 * Shortcode: `[book-reviews]` - Single book entry
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * @var array       $atts        Shortcode attributes.
 * @var Book        $book        Book object.
 * @var Review      $review      Review object.
 * @var Reading_Log $reading_log Reading log object.
 * @var object      $review_data Full object from the database.
 */

$rating       = new Rating( $review_data->rating ?? null );
$url          = $review->get_permalink();
$is_published = $review->is_published();
$target       = $review->is_external() ? 'blank' : 'self';
?>
<div id="review-<?php echo esc_attr( $review->get_id() ); ?>" class="book-review-entry bdb-grid-entry">
	<?php
	if ( $book->get_cover_id() ) {
		echo ( ! empty( $url ) && $is_published ) ? '<a href="' . esc_url( $url ) . '" target="_' . esc_attr( $target ) . '">' : '';
		echo $book->get_cover( $atts['cover-size'] );
		echo ( ! empty( $url ) && $is_published ) ? '</a>' : '';
	}

	if ( ! empty( $review_data->rating ) ) {
		?>
		<p class="book-review-rating">
			<?php echo $rating->format( get_option( 'rating_display', 'html_stars' ) ); ?>
		</p>
		<?php
	} elseif ( $reading_log->is_dnf() ) {
		?>
		<p class="book-review-rating">
			<?php _e( 'Did Not Finish', 'book-database' ) ?>
		</p>
		<?php
	}

	if ( ! $is_published ) {
		echo '<p>' . sprintf( __( 'Review coming %s', 'book-database' ), $review->get_date_published( true ) ) . '</p>';
	} elseif ( ! empty( $url ) ) {
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="button bdb-read-review-link" target="_<?php echo esc_attr( $target ); ?>"><?php _e( 'Read Review', 'book-database' ); ?></a>
		<?php
	}
	?>
</div>

