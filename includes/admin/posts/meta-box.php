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
	<p id="ubb-book-reviews-message"><?php _e( 'Below is a list of book reviews connected to this post. Deleting a review will delete it from your review archive and disassociate it with this post.', 'book-database' ); ?></p>

	<table class="wp-list-table widefat fixed posts">
		<thead>
		<tr>
			<th><?php _e( 'ID', 'book-database' ); ?></th>
			<th><?php _e( 'Book Title', 'book-database' ); ?></th>
			<th><?php _e( 'Author(s)', 'book-database' ); ?></th>
			<th><?php _e( 'Rating', 'book-database' ); ?></th>
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
					<td><?php echo esc_html( $book->get_title() ); ?></td>
					<td><?php echo esc_html( $book->get_author_names() ); ?></td>
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
						<button class="button secondary bdb-remove-book-review"><?php _e( 'Remove', 'book-database' ); ?></button>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr id="ubb-no-book-reviews-message" <?php echo ( empty( $reviews ) ) ? '' : 'style="display: none"'; ?>>
				<td colspan="4"><?php _e( 'No book reviews yet! Click the button below to add a review to this post. This will also add a new entry in your review archive.', 'book-database' ); ?></td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>
	<div id="ubb-add-review-buttons">
		<button id="ubb-add-review" class="button button-secondary"><?php esc_html_e( 'Add Review', 'book-database' ); ?></button>
	</div>
	<?php

	do_action( 'book-database/meta-box/post/book-reviews/after', $post );

	wp_nonce_field( 'bdb_save_post_book_reviews_meta', 'bdb_post_book_reviews_meta_box_nonce' );
}