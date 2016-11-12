<?php
/**
 * Shortcodes
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
 * Book Info Shortcode
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'id' => 0
	), $atts, 'book' );

	if ( ! $atts['id'] || ! is_numeric( $atts['id'] ) ) {
		return sprintf( __( 'Invalid book: %s', 'book-database' ), $atts['id'] );
	}

	$book      = new BDB_Book( absint( $atts['id'] ) );
	$book_info = $book->get_formatted_info();

	return apply_filters( 'book-database/shortcodes/book/output', $book_info, $book, $atts, $content );
}

add_shortcode( 'book', 'bdb_book_shortcode' );

/**
 * Review Index
 *
 * @param array  $atts
 * @param string $content
 *
 * @since 1.0.0
 * @return string
 */
function bdb_review_index_shortcode( $atts, $content = '' ) {
	$atts = shortcode_atts( array(
		'type'    => 'title', // title, author, series, publisher, genre
		'orderby' => 'title', // title, author, date, pub_date, series_position, pages
		'order'   => 'ASC', // ASC, DESC
		'letters' => 'yes' // yes, no
	), $atts, 'book' );

	$output = '';

	switch ( $atts['type'] ) {

		case 'title' :
			$index  = new BDB_Reviews_by_Title( $atts, $content );
			$output = $index->display();
			break;

		case 'series' :
			$index  = new BDB_Reviews_by_Series( $atts, $content );
			$output = $index->display();
			break;

		default :
			$taxonomies = bdb_get_taxonomies( true );

			if ( ! array_key_exists( $atts['type'], $taxonomies ) ) {
				break;
			}

			$index  = new BDB_Reviews_by_Tax( $atts, $content );
			$output = $index->display();
			break;

	}

	return '<div class="review-index review-index-by-' . sanitize_html_class( $atts['type'] ) . '">' . $output . '</div>';
}

add_shortcode( 'review-index', 'bdb_review_index_shortcode' );

/**
 * Reviews
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @since 1.0.0
 * @return string
 */
function bdb_book_reviews_shortcode( $atts, $content = '' ) {

	$args = array();

	// Book title
	if ( isset( $_GET['title'] ) ) {
		$args['book_title'] = wp_strip_all_tags( $_GET['title'] );
	}

	// Author
	if ( isset( $_GET['author'] ) ) {
		$args['author_name'] = wp_strip_all_tags( $_GET['author'] );
	}

	// Series
	if ( isset( $_GET['series_name'] ) ) {
		$args['series_name'] = wp_strip_all_tags( $_GET['series_name'] );
	}

	// Rating
	if ( isset( $_GET['rating'] ) && 'any' != $_GET['rating'] ) {
		$args['rating'] = wp_strip_all_tags( $_GET['rating'] );
	}

	// Orderby
	if ( isset( $_GET['orderby'] ) ) {
		$args['orderby'] = wp_strip_all_tags( $_GET['orderby'] );
	} else {
		$args['orderby'] = 'date';
	}

	// Order
	if ( isset( $_GET['order'] ) ) {
		$args['order'] = wp_strip_all_tags( $_GET['order'] );
	}

	$query    = new BDB_Review_Query( $args ); // @todo dynamic args
	$template = BDB_DIR . 'templates/shortcode-book-reviews-entry.php'; // @todo template function

	$current_rating  = ( isset( $_GET['rating'] ) && 'any' != $_GET['rating'] ) ? $_GET['rating'] : 'any';
	$current_orderby = ( isset( $_GET['orderby'] ) && 'date' != $_GET['orderby'] ) ? $_GET['orderby'] : 'date';
	$current_order   = ( isset( $_GET['order'] ) && 'ASC' == strtoupper( $_GET['order'] ) ) ? 'ASC' : 'DESC';

	ob_start();
	?>
	<form id="bookdb-filter-book-reviews" action="" method="GET">
		<p class="bookdb-filter-option">
			<label for="bookdb-book-title"><?php _e( 'Book Title', 'book-database' ); ?></label>
			<input type="text" id="bookdb-book-title" name="title" value="<?php echo isset( $_GET['title'] ) ? esc_attr( wp_strip_all_tags( $_GET['title'] ) ) : ''; ?>">
		</p>

		<p class="bookdb-filter-option">
			<label for="bookdb-book-author"><?php _e( 'Author', 'book-database' ); ?></label>
			<input type="text" id="bookdb-book-author" name="author" value="<?php echo isset( $_GET['author'] ) ? esc_attr( wp_strip_all_tags( $_GET['author'] ) ) : ''; ?>">
		</p>

		<p class="bookdb-filter-option">
			<label for="bookdb-book-series"><?php _e( 'Series', 'book-database' ); ?></label>
			<input type="text" id="bookdb-book-series" name="series" value="<?php echo isset( $_GET['series'] ) ? esc_attr( wp_strip_all_tags( $_GET['series'] ) ) : ''; ?>">
		</p>

		<p class="bookdb-filter-option">
			<label for="bookdb-book-rating"><?php _e( 'Rating', 'book-database' ); ?></label>
			<select id="bookdb-book-rating" name="rating">
				<option value="any"<?php selected( $current_rating, 'any' ) ?>><?php _e( 'Any', 'book-database' ); ?></option>
				<?php foreach ( bdb_get_available_ratings() as $value => $name ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_rating, $value ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p class="bookdb-filter-option">
			<label for="bookdb-orderby"><?php _e( 'Order by', 'book-database' ); ?></label>
			<select id="bookdb-orderby" name="orderby">
				<option value="date"<?php selected( $current_orderby, 'date' ) ?>><?php _e( 'Review Date', 'book-database' ); ?></option>
				<option value="title"<?php selected( $current_orderby, 'title' ) ?>><?php _e( 'Book Title', 'book-database' ); ?></option>
				<option value="author"<?php selected( $current_orderby, 'author' ) ?>><?php _e( 'Author Name', 'book-database' ); ?></option>
				<option value="pub_date"<?php selected( $current_orderby, 'pub_date' ) ?>><?php _e( 'Publication Date', 'book-database' ); ?></option>
				<option value="pages"<?php selected( $current_orderby, 'pages' ) ?>><?php _e( 'Number of Pages', 'book-database' ); ?></option>
			</select>
		</p>

		<p class="bookdb-filter-option">
			<label for="bookdb-order"><?php _e( 'Order', 'book-database' ); ?></label>
			<select id="bookdb-order" name="order">
				<option value="ASC"<?php selected( $current_order, 'ASC' ) ?>><?php _e( 'ASC (1, 2, 3; a, b, c)', 'book-database' ); ?></option>
				<option value="DESC"<?php selected( $current_order, 'DESC' ) ?>><?php _e( 'DESC (3, 2, 1; c, b, a', 'book-database' ); ?></option>
			</select>
		</p>

		<button type="submit"><?php _e( 'Filter', 'book-database' ); ?></button>

		<a href="<?php echo esc_url( get_permalink() ); ?>" class="button bookdb-reset-search-filters"><?php _e( 'Reset', 'book-database' ); ?></a>
	</form>
	<?php

	if ( $query->have_reviews() ) {
		echo '<div class="book-reviews-list">';
		foreach ( $query->get_reviews() as $entry ) {
			include $template;
		}
		echo '</div>';
	}

	echo $query->get_pagination();

	return ob_get_clean();

}

add_shortcode( 'book-reviews', 'bdb_book_reviews_shortcode' );