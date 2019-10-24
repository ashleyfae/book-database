<?php
/**
 * Shortcodes
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

function book_shortcode( $atts, $content = '' ) {

	$atts = shortcode_atts( array(
		'id'     => 0,
		'rating' => null
	), $atts, 'book' );

	if ( empty( $atts['id'] ) || ! is_numeric( $atts['id'] ) ) {
		return sprintf( __( 'Invalid book: %s', 'book-database' ), $atts['id'] );
	}

	$book = get_book( absint( $atts['id'] ) );

	if ( ! $book instanceof Book ) {
		return sprintf( __( 'Invalid book: %s', 'book-database' ), $atts['id'] );
	}

	$layout = new Book_Layout( $book );
	$layout->set_rating( $atts['rating'] );

	$html = $layout->get_html();

	/**
	 * Filters the [book] shortcode HTML.
	 *
	 * @param string $html    Formatted book layout.
	 * @param Book   $book    Book object.
	 * @param array  $atts    Shortcode attributes.
	 * @param string $content Shortcode content.
	 */
	return apply_filters( 'book-database/shortcodes/book/output', $html, $book, $atts, $content );

}

add_shortcode( 'book', __NAMESPACE__ . '\book_shortcode' );

function book_reviews_shortcode( $atts, $content = '' ) {

	$atts = shortcode_atts( array(
		'hide_future' => true,
		'per_page'    => 20,
		'filters'     => 'book_title book_author book_series rating genre publisher review_year orderby order'
	), $atts, 'book-reviews' );

	$query   = new Book_Reviews_Query( $atts );
	$filters = explode( ' ', $atts['filters'] );

	ob_start();
	?>
	<form id="bdb-filter-book-reviews" action="<?php echo esc_url( get_permalink() ); ?>" method="GET">
		<?php foreach ( $filters as $filter ) : ?>
			<div class="bdb-filter-option">
				<?php
				switch ( $filter ) {
					case 'book_title' :
						?>
						<label for="bdb-book-title"><?php _e( 'Book Title', 'book-database' ); ?></label>
						<input type="text" id="bdb-book-title" name="title" value="<?php echo ! empty( $_GET['title'] ) ? esc_attr( wp_strip_all_tags( $_GET['title'] ) ) : ''; ?>">
						<?php
						break;

					case 'book_author' :
						?>
						<label for="bdb-book-author"><?php _e( 'Book Author', 'book-database' ); ?></label>
						<input type="text" id="bdb-book-author" name="author" value="<?php echo ! empty( $_GET['author'] ) ? esc_attr( wp_strip_all_tags( $_GET['author'] ) ) : ''; ?>">
						<?php
						break;

					case 'book_series' :
						?>
						<label for="bdb-book-series"><?php _e( 'Book Series', 'book-database' ); ?></label>
						<input type="text" id="bdb-book-series" name="series" value="<?php echo ! empty( $_GET['series'] ) ? esc_attr( wp_strip_all_tags( $_GET['series'] ) ) : ''; ?>">
						<?php
						break;

					case 'rating' :
						$selected_rating = $_GET['rating'] ?? 'any';
						?>
						<label for="bdb-book-rating"><?php _e( 'Rating', 'book-database' ); ?></label>
						<select id="bdb-book-rating" name="rating">
							<option value="any" <?php selected( $selected_rating, 'any' ); ?>><?php _e( 'Any', 'book-database' ); ?></option>
							<?php foreach ( get_available_ratings() as $rating_value => $rating ) :
								$rating_object = new Rating( $rating_value );
								?>
								<option value="<?php echo esc_attr( $rating_value ); ?>" <?php selected( $selected_rating, $rating_value ); ?>><?php echo esc_html( $rating_object->format_text() ); ?></option>
							<?php endforeach; ?>
						</select>
						<?php
						break;

					case 'review_year' :
						break;

					case 'orderby' :
						$orderby = $_GET['orderby'] ?? 'review.date_published';
						?>
						<label for="bdb-orderby"><?php _e( 'Order By', 'book-database' ); ?></label>
						<select id="bdb-orderby" name="orderby">
							<option value="author.name" <?php selected( $orderby, 'author.name' ); ?>><?php _e( 'Author Name', 'book-database' ); ?></option>
							<option value="book.title" <?php selected( $orderby, 'book.title' ); ?>><?php _e( 'Book Title', 'book-database' ); ?></option>
							<option value="book.pub_date" <?php selected( $orderby, 'book.pub_date' ); ?>><?php _e( 'Book Publish Date', 'book-database' ); ?></option>
							<option value="log.date_finished" <?php selected( $orderby, 'log.date_finished' ); ?>><?php _e( 'Date Read', 'book-database' ); ?></option>
							<option value="review.date_published" <?php selected( $orderby, 'review.date_published' ); ?>><?php _e( 'Date Reviewed', 'book-database' ); ?></option>
							<option value="book.pages" <?php selected( $orderby, 'book.pages' ); ?>><?php _e( 'Number of Pages', 'book-database' ); ?></option>
							<option value="log.rating" <?php selected( $orderby, 'log.rating' ); ?>><?php _e( 'Rating', 'book-database' ); ?></option>
						</select>
						<?php
						break;

					case 'order' :
						break;

					// Taxonomies
					default :
						$taxonomy = get_book_taxonomy_by( $filter, 'slug' );

						if ( ! $taxonomy instanceof Book_Taxonomy ) {
							break;
						}
						break;
				}
				?>
			</div>
		<?php endforeach; ?>
		<div class="bdb-filter-actions">
			<button type="submit"><?php _e( 'Filter', 'book-database' ); ?></button>
			<a href="<?php echo esc_url( get_permalink() ); ?>" class="bdb-reset-search-filters"><?php _e( 'Clear filters &times;', 'book-database' ); ?></a>
		</div>
	</form>
	<div id="bdb-reviews">
		<?php
		$reviews = $query->get_reviews();

		if ( ! empty( $reviews ) ) {
			echo '<div class="bdb-review-list-number-results">' . sprintf( _n( '%s review found', '%s reviews found', $query->total_results, 'book-database' ), $query->total_results ) . '</div>';
			echo '<div class="bdb-book-reviews-list">';
			foreach ( $reviews as $review ) {
				//include $template;
			}
			echo '</div>';
		} else {
			?>
			<p><?php _e( 'No reviews found.', 'book-database' ); ?></p>
			<?php
		}
		?>
		<nav class="bdb-reviews-list-pagination pagination">
			<?php $query->get_pagination(); ?>
		</nav>
	</div>
	<?php

	return ob_get_clean();

}

add_shortcode( 'book-reviews', __NAMESPACE__ . '\book_reviews_shortcode' );