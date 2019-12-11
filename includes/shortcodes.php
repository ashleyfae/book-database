<?php
/**
 * Shortcodes
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Book
 *
 * Display book information.
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @return string
 */
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

/**
 * Book reviews
 *
 * Displays a list of book reviews with filters allowing users to change the parameters.
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @return string
 */
function book_reviews_shortcode( $atts, $content = '' ) {

	global $wp_query;

	$atts = shortcode_atts( array(
		'hide-future' => true,
		'per-page'    => 20,
		'filters'     => 'book_title book_author book_series rating genre publisher review_year orderby order',
		'cover-size'  => 'medium'
	), $atts, 'book-reviews' );

	$query   = new Book_Reviews_Query( $atts );
	$filters = explode( ' ', $atts['filters'] );

	ob_start();
	?>
	<form id="bdb-filter-book-reviews" action="<?php echo esc_url( get_permalink() ); ?>" method="GET">
		<div class="bdb-filters">
			<?php foreach ( $filters as $filter ) : ?>
				<div class="bdb-filter-option">
					<?php
					switch ( $filter ) {
						case 'book_title' :
							$title = '';
							if ( ! empty( $_GET['title'] ) ) {
								$title = wp_strip_all_tags( $_GET['title'] );
							}
							?>
							<label for="bdb-book-title"><?php _e( 'Book Title', 'book-database' ); ?></label>
							<input type="text" id="bdb-book-title" name="title" value="<?php echo esc_attr( $title ); ?>">
							<?php
							break;

						case 'book_author' :
							$author = '';
							if ( ! empty( $_GET['author'] ) ) {
								$author = wp_strip_all_tags( $_GET['author'] );
							} elseif ( ! empty( $wp_query->query_vars['book_tax'] ) && 'author' === $wp_query->query_vars['book_tax'] && ! empty( $wp_query->query_vars['book_term'] ) ) {
								$slug   = $wp_query->query_vars['book_term'];
								$author = get_book_author_by( 'slug', $slug );
								$author = $author instanceof Author ? $author->get_name() : '';
							}
							?>
							<label for="bdb-book-author"><?php _e( 'Book Author', 'book-database' ); ?></label>
							<input type="text" id="bdb-book-author" name="author" value="<?php echo esc_attr( $author ); ?>">
							<?php
							break;

						case 'book_series' :
							$series = '';
							if ( ! empty( $_GET['series'] ) ) {
								$series = wp_strip_all_tags( $_GET['series'] );
							} elseif ( ! empty( $wp_query->query_vars['book_tax'] ) && 'series' === $wp_query->query_vars['book_tax'] && ! empty( $wp_query->query_vars['book_term'] ) ) {
								$slug   = $wp_query->query_vars['book_term'];
								$series = get_book_series_by( 'slug', $slug );
								$series = $series instanceof Series ? $series->get_name() : '';
							}
							?>
							<label for="bdb-book-series"><?php _e( 'Book Series', 'book-database' ); ?></label>
							<input type="text" id="bdb-book-series" name="series" value="<?php echo esc_attr( $series ); ?>">
							<?php
							break;

						case 'rating' :
							$selected_rating = 'any';
							if ( ! empty( $_GET['rating'] ) ) {
								$selected_rating = wp_strip_all_tags( $_GET['rating'] );
							} elseif ( ! empty( $wp_query->query_vars['book_tax'] ) && 'rating' === $wp_query->query_vars['book_tax'] && ! empty( $wp_query->query_vars['book_term'] ) ) {
								$selected_rating = $wp_query->query_vars['book_term'];
							}
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
							$years = get_review_years( 'published' );
							$selected_year = $_GET['review_year'] ?? 'any';
							?>
							<label for="bdb-book-review-year"><?php _e( 'Review Year', 'book-database' ); ?></label>
							<select id="bdb-book-review-year" name="review_year">
								<option value="any" <?php selected( $selected_year, 'any' ); ?>><?php _e( 'Any', 'book-database' ); ?></option>
								<?php foreach ( $years as $year ) : ?>
									<option value="<?php echo esc_attr( $year ); ?>" <?php selected( $selected_year, $year ); ?>><?php echo esc_html( $year ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php
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
							$order = $_GET['order'] ?? 'DESC';
							?>
							<label for="bdb-order"><?php _e( 'Order', 'book-database' ); ?></label>
							<select id="bdb-order" name="order">
								<option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php _e( 'ASC (1, 2, 3; a, b, c)', 'book-database' ); ?></option>
								<option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php _e( 'DESC (3, 2, 1; c, b, a)', 'book-database' ); ?></option>
							</select>
							<?php
							break;

						// Taxonomies
						default :
							$taxonomy = get_book_taxonomy_by( 'slug', $filter );

							if ( ! $taxonomy instanceof Book_Taxonomy ) {
								break;
							}

							$terms = get_book_terms( array(
								'taxonomy' => $taxonomy->get_slug(),
								'number'   => 50,
								'orderby'  => 'name',
								'order'    => 'ASC'
							) );

							$selected_term = 'any';
							if ( ! empty( $_GET[$taxonomy->get_slug()] ) ) {
								$selected_term = wp_strip_all_tags( $taxonomy->get_slug() );
							} elseif ( ! empty( $wp_query->query_vars['book_tax'] ) && $taxonomy->get_slug() === $wp_query->query_vars['book_tax'] && ! empty( $wp_query->query_vars['book_term'] ) ) {
								$slug = $wp_query->query_vars['book_term'];
								$term = get_book_term_by( 'slug', $slug );

								if ( $term instanceof Book_Term ) {
									$selected_term = $term->get_id();
								}
							}
							?>
							<label for="bdb-<?php echo esc_attr( $taxonomy->get_slug() ); ?>"><?php echo esc_html( $taxonomy->get_name() ) ?></label>
							<select id="bdb-<?php echo esc_attr( $taxonomy->get_slug() ); ?>" name="<?php echo esc_attr( $taxonomy->get_slug() ); ?>">
								<option value="any" <?php selected( $selected_term, 'any' ); ?>><?php _e( 'Any', 'book-database' ); ?></option>
								<?php foreach ( $terms as $term ) : ?>
									<option value="<?php echo esc_attr( $term->get_id() ); ?>" <?php selected( $selected_term, $term->get_id() ); ?>><?php echo esc_html( $term->get_name() ); ?></option>
								<?php endforeach; ?>
							</select>
							<?php
							break;
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="bdb-filter-actions">
			<button type="submit"><?php _e( 'Filter', 'book-database' ); ?></button>
			<a href="<?php echo esc_url( get_permalink() ); ?>" class="bdb-reset-search-filters"><?php _e( 'Clear filters &times;', 'book-database' ); ?></a>
		</div>
	</form>
	<div id="bdb-reviews">
		<?php
		$reviews  = $query->get_results();
		$template = get_book_template_part( 'shortcode-book-reviews-entry', '', false );

		if ( ! empty( $reviews ) ) {
			echo '<div class="bdb-review-list-number-results bdb-book-grid-number-results">' . sprintf( _n( '%s review found', '%s reviews found', $query->total_results, 'book-database' ), $query->total_results ) . '</div>';
			echo '<div class="bdb-book-reviews-list bdb-book-grid">';
			foreach ( $reviews as $review_data ) {
				$review      = new Review( $review_data );
				$book        = new Book( array(
					'id'              => $review_data->book_id ?? 0,
					'cover_id'        => $review_data->book_cover_id ?? null,
					'title'           => $review_data->book_title ?? '',
					'pub_date'        => $review_data->book_pub_date ?? '',
					'series_position' => $review_data->series_position ?? ''
				) );
				$reading_log = new Reading_Log( array(
					'date_started'        => $review_data->date_started_reading ?? null,
					'date_finished'       => $review_data->date_finished_reading ?? null,
					'percentage_complete' => $review_data->percentage_complete ?? null,
					'rating'              => $review_data->rating ?? null
				) );
				include $template;
			}
			echo '</div>';
		} else {
			?>
			<p><?php _e( 'No reviews found.', 'book-database' ); ?></p>
			<?php
		}
		?>
		<nav class="bdb-pagination bookdb-reviews-list-pagination pagination">
			<?php echo $query->get_pagination(); ?>
		</nav>
	</div>
	<?php

	return ob_get_clean();

}

add_shortcode( 'book-reviews', __NAMESPACE__ . '\book_reviews_shortcode' );

/**
 * Book grid
 *
 * Similar to `[book-reviews]` but filtering is done via shortcode attributes instead of
 * a front-end form. It also focuses on books rather than reviews.
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @return string
 */
function book_grid_shortcode( $atts, $content = '' ) {

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
		'review-start-date'   => '',
		'review-end-date'     => '',
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

	foreach ( get_book_taxonomies( array( 'fields' => 'slug' ) ) as $tax_slug ) {
		$default_atts[$tax_slug] = '';
	}

	$atts = shortcode_atts( $default_atts, $atts, 'book-grid' );

	// Replace "review-start-date" and "review-end-date".
	if ( ! empty( $atts['review-start-date'] ) ) {
		$atts['review-date-after'] = $atts['review-start-date'];
	}
	if ( ! empty( $atts['review-end-date'] ) ) {
		$atts['review-date-before'] = $atts['review-end-date'];
	}

	$query = new Book_Grid_Query( $atts );

	ob_start();
	?>
	<div id="bdb-books">
		<?php
		$books    = $query->get_results();
		$template = get_book_template_part( 'shortcode-book-grid-entry', '', false );

		if ( ! empty( $books ) ) {
			echo '<div class="bdb-book-list-number-results bdb-book-grid-number-results">' . sprintf( _n( '%s book found', '%s books found', $query->total_results, 'book-database' ), $query->total_results ) . '</div>';
			echo '<div class="bdb-book-list bdb-book-grid">';
			foreach ( $books as $book_data ) {
				$book   = new Book( $book_data );
				$review = false;

				// Create a review object if we can.
				if ( ! empty( $book_data->review_id ) ) {
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

					$review = new Review( $review_data );
				}

				include $template;
			}
			echo '</div>';
		} else {
			?>
			<p><?php _e( 'No books found.', 'book-database' ); ?></p>
			<?php
		}
		?>
		<nav class="bdb-pagination bdb-book-grid-pagination pagination">
			<?php echo $query->get_pagination(); ?>
		</nav>
	</div>
	<?php
	return ob_get_clean();

}

add_shortcode( 'book-grid', __NAMESPACE__ . '\book_grid_shortcode' );