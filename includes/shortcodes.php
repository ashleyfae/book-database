<?php
/**
 * Shortcodes
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
		'id'     => 0,
		'rating' => null
	), $atts, 'book' );

	if ( ! $atts['id'] || ! is_numeric( $atts['id'] ) ) {
		return sprintf( __( 'Invalid book: %s', 'book-database' ), $atts['id'] );
	}

	$book = new BDB_Book( absint( $atts['id'] ) );
	$book->set_rating( $atts['rating'] );
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
		'type'    => 'title', // title, author, series, publisher, genre @todo year
		'orderby' => 'title', // title, author, date, pub_date, series_position, pages
		'order'   => 'ASC', // ASC, DESC
		'letters' => 'yes' // yes, no
	), $atts, 'review-index' );

	$output = '';

	// Check for cache.
	$cache_key = 'bookdb_review_index_' . serialize( $atts );
	$cache     = get_transient( $cache_key );

	if ( $cache ) {
		$output = $cache;
	} else {

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

		if ( $output ) {
			set_transient( $cache_key, $output, DAY_IN_SECONDS );
		}

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

	$args                  = array(
		'hide_future' => true,
		'reviews'     => 'only'
	);
	$query                 = new BDB_Book_Query( apply_filters( 'book-database/shortcodes/book-reviews/query-args', $args, $atts ), 'reviews' );
	$query->table_log_join = true;
	$vars                  = $query->parse_query_args();
	$query->query();
	$template = bdb_get_template_part( 'shortcode-book-reviews-entry', '', false );

	ob_start();
	?>
	<form id="bookdb-filter-book-reviews" action="<?php echo esc_url( get_permalink() ); ?>" method="GET">
		<?php do_action( 'book-database/shortcodes/book-reviews/filter-form', wp_unslash( $vars ), $query, $atts, $content ); ?>
	</form>
	<?php

	echo '<div id="reviews">';

	if ( $query->have_books() && ! empty( $template ) ) {
		echo '<div class="bookdb-review-list-number-results">' . sprintf( _n( '%s review found', '%s reviews found', $query->total_books, 'book-database' ), $query->total_books ) . '</div>';
		echo '<div class="book-reviews-list">';
		foreach ( $query->get_books() as $entry ) {
			include $template;
		}
		echo '</div>';
	} else {
		echo '<p>' . __( 'No reviews found.', 'book-database' ) . '</p>';
	}

	echo '<nav class="pagination bookdb-reviews-list-pagination">' . $query->get_pagination() . '</nav>';

	echo '</div>';

	return ob_get_clean();

}

add_shortcode( 'book-reviews', 'bdb_book_reviews_shortcode' );

/**
 * Book Grid
 *
 * Similar to `[book-reviews]` but filtering is done via shortcode attributes
 * instead of a front-end form. It can also show books that have not been reviewed.
 *
 * @param array  $atts    Shortcode attributes.
 * @param string $content Shortcode content.
 *
 * @since 1.3.0
 * @return string
 */
function bdb_book_grid_shortcode( $atts, $content = '' ) {

	$default_atts = array(
		'ids'                 => false, // for specific books
		'author'              => false,
		'series'              => false,
		'rating'              => false,
		'year'                => false, // review written year
		'month'               => false, // review written month
		'day'                 => false, // review written day
		'start-date'          => false, // pub start date
		'end-date'            => false, // pub end date
		'review-start-date'   => false, // review pub start date
		'review-end-date'     => false, // review pub end date
		'pub-year'            => false,
		'show-ratings'        => false,
		'show-review-link'    => false,
		'show-goodreads-link' => false,
		'reviews-only'        => false,
		'orderby'             => 'id',
		'order'               => 'DESC',
		'image-size'          => 'large',
		'number'              => 20
	);

	foreach ( bdb_get_taxonomies() as $id => $options ) {
		$default_atts[ $id ] = false;
	}

	$atts = shortcode_atts( $default_atts, $atts, 'book-grid' );

	$query_args = $term_args = array();

	$query_args['ids']         = ! empty( $atts['ids'] ) ? explode( ',', $atts['ids'] ) : null;
	$query_args['author_name'] = $atts['author'];
	$query_args['series_name'] = $atts['series'];
	$query_args['rating']      = $atts['rating'];
	$query_args['year']        = $atts['year'];
	$query_args['month']       = $atts['month'];
	$query_args['day']         = $atts['day'];
	$query_args['pub_year']    = $atts['pub-year'];
	$query_args['orderby']     = $atts['orderby'];
	$query_args['order']       = $atts['order'];
	$query_args['number']      = intval( $atts['number'] );

	// Setup book publish date
	if ( $atts['start-date'] ) {
		$query_args['pub_date']['start'] = $atts['start-date'];
	}
	if ( $atts['end-date'] ) {
		$query_args['pub_date']['end'] = $atts['end-date'];
	}

	// Setup review publish date
	if ( $atts['review-start-date'] ) {
		$query_args['review_date']['start'] = $atts['review-start-date'];
	}
	if ( $atts['review-end-date'] ) {
		$query_args['review_date']['end'] = $atts['review-end-date'];
	}

	// Setup terms.
	foreach ( bdb_get_taxonomies() as $id => $options ) {
		if ( array_key_exists( $id, $atts ) && false !== $atts[ $id ] && is_numeric( $atts[ $id ] ) ) {
			$term_args[ $id ] = absint( $atts[ $id ] );
		}
	}

	if ( ! empty( $term_args ) ) {
		$query_args['terms'] = $term_args;
	}

	$type  = ( $atts['reviews-only'] ) ? 'reviews' : 'books';
	$query = new BDB_Book_Query( $query_args, $type );

	if ( $atts['show-ratings'] ) {
		$query->table_log_join = true;
	}
	if ( $atts['show-review-link'] ) {
		$query->table_reviews_join = true;
	}

	$query->query();
	$template = bdb_get_template_part( 'shortcode-book-grid-entry', '', false );

	ob_start();

	if ( $query->have_books() && ! empty( $template ) ) {
		echo '<div class="book-reviews-list">';
		foreach ( $query->get_books() as $entry ) {
			include $template;
		}
		echo '</div>';
	} else {
		echo '<p>' . __( 'No books found.', 'book-database' ) . '</p>';
	}

	return ob_get_clean();

}

add_shortcode( 'book-grid', 'bdb_book_grid_shortcode' );

/**
 * Filter: Title
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_title( $vars, $query, $atts, $content ) {
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-book-title"><?php _e( 'Book Title', 'book-database' ); ?></label>
		<input type="text" id="bookdb-book-title" name="title" value="<?php echo esc_attr( wp_strip_all_tags( $vars['book_title'] ) ); ?>">
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_title', 10, 4 );

/**
 * Filter: Author
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_author( $vars, $query, $atts, $content ) {
	$author_name = $vars['author_name'];

	if ( isset( $vars['terms']['author'] ) ) {
		$author = bdb_get_term( array(
			'term_id' => absint( $vars['terms']['author'] ),
			'fields'  => 'names'
		) );

		if ( $author ) {
			$author_name = $author;
		}
	}
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-book-author"><?php _e( 'Author', 'book-database' ); ?></label>
		<input type="text" id="bookdb-book-author" name="author" value="<?php echo esc_attr( wp_strip_all_tags( $author_name ) ); ?>">
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_author', 10, 4 );

/**
 * Filter: Series
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_series( $vars, $query, $atts, $content ) {
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-book-series"><?php _e( 'Series', 'book-database' ); ?></label>
		<input type="text" id="bookdb-book-series" name="series" value="<?php echo esc_attr( wp_strip_all_tags( $vars['series_name'] ) ); ?>">
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_series', 10, 4 );

/**
 * Filter: Rating
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_rating( $vars, $query, $atts, $content ) {
	$current_rating = $vars['rating'] ? $vars['rating'] : 'any';
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-book-rating"><?php _e( 'Rating', 'book-database' ); ?></label>
		<select id="bookdb-book-rating" name="rating">
			<option value="any"<?php selected( $current_rating, 'any' ) ?>><?php _e( 'Any', 'book-database' ); ?></option>
			<?php foreach ( bdb_get_available_ratings() as $value => $name ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current_rating, $value ); ?>><?php echo esc_html( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_rating', 10, 4 );

/**
 * Filter: Genre
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_genre( $vars, $query, $atts, $content ) {
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-genre"><?php _e( 'Genre', 'book-database' ); ?></label>
		<?php echo book_database()->html->term_dropdown( 'genre', array(
			'id'       => 'bookdb-genre',
			'selected' => isset( $vars['terms']['genre'] ) ? absint( $vars['terms']['genre'] ) : 0
		) ); ?>
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_genre', 10, 4 );

/**
 * Filter: Publisher
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_publisher( $vars, $query, $atts, $content ) {
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-publisher"><?php _e( 'Publisher', 'book-database' ); ?></label>
		<?php echo book_database()->html->term_dropdown( 'publisher', array(
			'id'       => 'bookdb-publisher',
			'selected' => isset( $vars['terms']['publisher'] ) ? absint( $vars['terms']['publisher'] ) : 0
		) ); ?>
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_publisher', 10, 4 );

/**
 * Filter: Review Year
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_review_year( $vars, $query, $atts, $content ) {
	$review_years = bdb_get_review_years( 'written' );
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-review-year"><?php _e( 'Review Year', 'book-database' ); ?></label>
		<?php echo book_database()->html->select( array(
			'id'               => 'bookdb-review-year',
			'name'             => 'review_year',
			'options'          => $review_years,
			'show_option_all'  => esc_html__( 'Any', 'book-database' ),
			'show_option_none' => false,
			'selected'         => isset( $vars['year'] ) ? $vars['year'] : 'any'
		) ); ?>
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_review_year', 10, 4 );

/**
 * Filter: Order By
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_orderby( $vars, $query, $atts, $content ) {

	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-orderby"><?php _e( 'Order by', 'book-database' ); ?></label>
		<?php echo book_database()->html->select( array(
			'id'               => 'bookdb-orderby',
			'name'             => 'orderby',
			'selected'         => sanitize_text_field( $vars['orderby'] ),
			'options'          => apply_filters( 'book-database/shortcodes/book-reviews/orderby-options', bdb_get_allowed_orderby() ),
			'show_option_all'  => false,
			'show_option_none' => false
		) ); ?>
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_orderby', 10, 4 );

/**
 * Filter: Order
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_order( $vars, $query, $atts, $content ) {
	$allowed_order = array(
		'ASC'  => esc_html__( 'ASC (1, 2, 3; a, b, c)', 'book-database' ),
		'DESC' => esc_html__( 'DESC (3, 2, 1; c, b, a)', 'book-database' )
	);
	?>
	<p class="bookdb-filter-option">
		<label for="bookdb-order"><?php _e( 'Order', 'book-database' ); ?></label>
		<?php echo book_database()->html->select( array(
			'id'               => 'bookdb-order',
			'name'             => 'order',
			'selected'         => sanitize_text_field( $vars['order'] ),
			'options'          => apply_filters( 'book-database/shortcodes/book-reviews/order-options', $allowed_order ),
			'show_option_all'  => false,
			'show_option_none' => false
		) ); ?>
	</p>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_order', 10, 4 );

/**
 * Filter: Submit Button
 *
 * @param array          $vars    Query vars.
 * @param BDB_Book_Query $query   Review query.
 * @param array          $atts    Shortcode attributes.
 * @param string         $content Shortcode content.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_reviews_filter_form_submit( $vars, $query, $atts, $content ) {
	?>
	<div class="bookdb-filter-actions">
		<button type="submit"><?php _e( 'Filter', 'book-database' ); ?></button>
		<a href="<?php echo esc_url( get_permalink() ); ?>" class="bookdb-reset-search-filters"><?php _e( 'Clear filters &times;', 'book-database' ); ?></a>
	</div>
	<?php
}

add_action( 'book-database/shortcodes/book-reviews/filter-form', 'bdb_reviews_filter_form_submit', 200, 4 );