<?php
/**
 * Rewrite Rules
 *
 * Used for creating the dynamic taxonomy archives.
 *
 * @todo Merge this shit with [book-reviews]
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
 * Get Reviews Page URL
 *
 * @since 1.0.0
 * @return string|false
 */
function bdb_get_reviews_page_url() {
	$page_id = bdb_get_option( 'reviews_page' );
	$url     = false;

	if ( $page_id ) {
		$url = get_permalink( absint( $page_id ) );
	}

	return apply_filters( 'book-database/reviews-page-url', $url );
}

/**
 * Get Reviews Endpoint
 *
 * @since 1.0.0
 * @return string
 */
function bdb_get_reviews_endpoint() {
	return apply_filters( 'book-database/rewrite/endpoint', 'reviews' );
}

/**
 * Register Rewrite Tags
 *
 * @since 1.0.0
 * @return void
 */
function bdb_rewrite_tags() {
	add_rewrite_tag( '%book_tax%', '([^&]+)' );
	add_rewrite_tag( '%book_term%', '([^&]+)' );
}

add_action( 'init', 'bdb_rewrite_tags' ); // @todo add to install

/**
 * Create Rewrite Rules
 *
 * @since 1.0.0
 * @return void
 */
function bdb_rewrite_rules() {
	$page_id = bdb_get_option( 'reviews_page' );

	if ( ! $page_id ) {
		return;
	}

	add_rewrite_rule( '^' . bdb_get_reviews_endpoint() . '/([^/]*)/([^/]*)/?', 'index.php?page_id=' . absint( $page_id ) . '&book_tax=$matches[1]&book_term=$matches[2]', 'top' );
}

add_action( 'init', 'bdb_rewrite_rules' ); // @todo add to install

/**
 * Rewrite Review Page Content
 *
 * If the tax/term query vars are present then rewrite the page to
 * show that specific archive.
 *
 * @param string $content
 *
 * @since 1.0.0
 * @return string
 */
function bdb_rewrite_review_page_content( $content ) {
	if ( get_the_ID() != bdb_get_option( 'reviews_page' ) ) {
		return $content;
	}

	global $wp_query;

	if ( ! array_key_exists( 'book_tax', $wp_query->query_vars ) || ! array_key_exists( 'book_term', $wp_query->query_vars ) ) {
		return $content;
	}

	$tax  = $wp_query->query_vars['book_tax'];
	$term = $wp_query->query_vars['book_term'];

	if ( empty( $tax ) || empty( $term ) ) {
		return $content;
	}

	$output = '';
	$books  = false;

	switch ( $tax ) {

		case 'author' :
			$author = bdb_get_term( array( 'type' => 'author', 'slug' => $term ) );
			if ( $author ) {
				$output .= '<h2>' . esc_html( $author->name ) . '</h2>';

				$books = bdb_get_books( array(
					'author_id' => $author->term_id,
					'orderby'   => 'pub_date',
					'order'     => 'ASC'
				) );
			}
			break;

		case 'series' :
			// @todo
			break;

		default :
			$taxonomies = bdb_get_taxonomies();
			if ( ! array_key_exists( $tax, $taxonomies ) ) {
				break;
			}

	}

	if ( is_array( $books ) ) {
		foreach ( $books as $book ) {
			$book    = new BDB_Book( $book );
			$reviews = bdb_get_book_reviews( $book->ID );

			// Remove reviews with no URL at all.
			foreach ( $reviews as $key => $review ) {
				if ( empty( $review->post_id ) && empty( $review->url ) ) {
					unset( $reviews[ $key ] );
				}
			}

			ob_start();
			?>
			<div>
				<img src="<?php echo esc_url( $book->get_cover_url( 'thumbnail' ) ); ?>" class="alignleft">
				<p><strong><?php echo $book->get_title(); ?></strong></p>

				<?php
				if ( $reviews ) {
					echo _n( 'Review:', 'Review', count( $reviews ), 'book-database' );
				}
				?>
			</div>
			<?php

			$output .= ob_get_clean();
		}
	}

	return $output;
}

add_filter( 'the_content', 'bdb_rewrite_review_page_content' );