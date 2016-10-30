<?php
/**
 * Add Third Party Review
 *
 * Functions for inserting reviews that aren't connected to blog posts.
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

function bdb_add_review_thickbox_markup() {
	?>
	<script type="text/javascript">
		function insertReview() {
			var id = jQuery('#bdb_books').val();

			// Return early if no book is selected
			if ('' === id) {
				alert('<?php _e( "You must choose a book", "ubb" ); ?>');
				return;
			}

			tb_remove();
		}
	</script>

	<div id="insert-review" style="display: none;">
		<div class="wrap">
			<h3><?php _e( 'Insert External Review', 'book-database' ); ?></h3>
			<p><?php echo sprintf( __( 'This form is for manually adding reviews to your archive that aren\'t connected to blog posts (i.e. reviews only on Goodreads).', 'book-database' ), bdb_get_label_singular() ); ?></p>
			<p><?php _e( 'If you want to add a review that <strong>is</strong> connected to a blog post then do it through the Edit Post interface.', 'book-database' ); ?></p>

			<p>
				<label for="bdb_review_book"><?php _e( 'Book', 'book-database' ); ?></label>
				<select id="bdb_review_book" name="bdb_review_book">

				</select>
			</p>

			<p>
				<label for="bdb_review_rating"><?php _e( 'Rating', 'book-database' ); ?></label>
				<select id="bdb_review_rating" name="bdb_review_rating"></select>
			</p>

			<p>
				<label for="bdb_review_url"><?php _e( 'Review URL', 'book-database' ); ?></label>
				<input type="url" id="bdb_review_url" name="bdb_review_url" placeholder="http://" value="">
			</p>

			<p>
				<label for="bdb_review_user"><?php _e( 'Reviewer (WordPress user account', 'book-database' ); ?></label>
				<select id="bdb_review_user" name="bdb_review_user"></select>
			</p>

			<p class="submit">
				<input type="button" id="ubb-insert-book" class="button-primary" value="<?php esc_attr_e( 'Insert Review', 'book-database' ); ?>" onclick="insertReview();">
				<a id="ubb-cancel-book-insert" class="button-secondary" onclick="tb_remove();"><?php _e( 'Cancel', 'book-database' ); ?></a>
			</p>
		</div>
	</div>
	<?php
}

add_action( 'admin_footer', 'bdb_add_review_thickbox_markup' );