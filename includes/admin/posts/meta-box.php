<?php
/**
 * Register and Display Meta Boxes
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley GIbson
 * @license   GPL2+
 * @since     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all meta boxes for posts/pages and other post types that
 * support adding bookr eviews.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_add_post_meta_boxes() {
	foreach ( bdb_get_review_post_types() as $post_type ) {
		add_meta_box( 'bdb_book_reviews', sprintf( __( '%s Reviews', 'book-database' ), bdb_get_label_singular() ), 'bdb_render_post_book_reviews_meta_box', $post_type, 'normal', 'high' );
	}
}

add_action( 'add_meta_boxes', 'bdb_add_post_meta_boxes' );

/**
 * Render Book Reviews Meta Box
 *
 * @param WP_Post $post
 *
 * @since 1.0.0
 * @return void
 */
function bdb_render_post_book_reviews_meta_box( $post ) {
	do_action( 'book-database/meta-box/post/book-reviews/before', $post );

	$reviews = bdb_get_post_reviews( $post->ID );
	?>
	<p id="bookdb-book-reviews-message"><?php _e( 'Below is a list of book reviews connected to this post. Deleting a review will delete it from your review archive and disassociate it with this post.', 'book-database' ); ?></p>

	<table class="wp-list-table widefat fixed posts">
		<thead>
		<tr>
			<th><?php _e( 'ID', 'book-database' ); ?></th>
			<th><?php _e( 'Book', 'book-database' ); ?></th>
			<th><?php _e( 'Rating', 'book-database' ); ?></th>
			<th><?php _e( 'Shortcode', 'book-database' ); ?></th>
			<th><?php _e( 'Remove', 'book-database' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php if ( $reviews && is_array( $reviews ) ) : ?>
			<?php foreach ( $reviews as $review_details ) :
				$book = new BDB_Book( $review_details->book_id );
				?>
				<tr data-id="<?php echo esc_attr( $review_details->ID ); ?>">
					<td>
						<?php echo $review_details->ID; ?>
						<a href="<?php echo esc_url( bdb_get_admin_page_edit_review( $review_details->ID ) ); ?>" target="_blank"><?php _e( '(Edit)', 'book-database' ); ?></a>
					</td>
					<td>
						<?php echo esc_html( sprintf( _x( '%s by %s', 'book title by author name', 'book-database' ), $book->get_title(), $book->get_author_names() ) ); ?>
					</td>
					<td>
						<?php
						if ( $review_details->rating ) {
							$rating = new BDB_Rating( $review_details->rating );
							echo $rating->format( 'text' );
						} else {
							echo '&ndash';
						}
						?>
					</td>
					<td>
						<?php
						if ( $review_details->book_id ) {
							echo '<code>[book id="' . esc_attr( $review_details->book_id ) . '"]</code>';
						}
						?>
					</td>
					<td>
						<button class="button secondary bdb-remove-book-review"><?php _e( 'Remove', 'book-database' ); ?></button>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr id="bookdb-no-book-reviews-message" <?php echo ( empty( $reviews ) ) ? '' : 'style="display: none"'; ?>>
				<td colspan="4"><?php _e( 'No book reviews yet! Click the button below to add a review to this post. This will also add a new entry in your review archive.', 'book-database' ); ?></td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
	<div id="bookdb-add-review-buttons">
		<button id="bookdb-add-review" class="button button-secondary"><?php esc_html_e( 'Add Review', 'book-database' ); ?></button>
	</div>

	<div id="bookdb-add-review-fields">
		<p><?php _e( 'Search for a book to review.', 'book-database' ); ?></p>
		<div id="bookdb-add-review-search-for-book">
			<label for="bookdb-add-review-search-book-input" class="screen-reader-text"><?php _e( 'Search for a book by title or author', 'book-database' ); ?></label>
			<input type="text" id="bookdb-add-review-search-book-input" placeholder="<?php esc_attr_e( 'Title or author name', 'book-database' ); ?>">

			<label for="book-db-add-review-search-type" class="screen-reader-text"><?php _e( 'Choose a field to search by', 'book-database' ); ?></label>
			<select id="book-db-add-review-search-type">
				<option value="title" selected><?php esc_html_e( 'Title', 'book-database' ); ?></option>
				<option value="author"><?php esc_html_e( 'Author', 'book-database' ); ?></option>
			</select>

			<button type="button" class="button"><?php esc_html_e( 'Search for Book', 'book-database' ); ?></button>
		</div>
	</div>
	<?php

	do_action( 'book-database/meta-box/post/book-reviews/after', $post );

	wp_nonce_field( 'bdb_save_post_book_reviews_meta', 'bdb_post_book_reviews_meta_box_nonce' );
}