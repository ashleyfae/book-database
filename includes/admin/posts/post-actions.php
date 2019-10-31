<?php
/**
 * Register and Display Post Meta Boxes
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Register meta boxes for all post types that support book reviews
 *
 * @since 1.0
 */
function register_meta_boxes() {
	if ( ! user_can_edit_books() ) {
		return;
	}

	foreach ( get_review_post_types() as $post_type ) {
		add_meta_box( 'bdb_book_reviews', __( 'Book Reviews', 'book-database' ), __NAMESPACE__ . '\render_book_reviews_meta_box', $post_type, 'normal', 'high' );
	}
}

add_action( 'add_meta_boxes', __NAMESPACE__ . '\register_meta_boxes' );

/**
 * Render the book reviews meta box
 *
 * @param \WP_Post $post
 *
 * @since 1.0
 */
function render_book_reviews_meta_box( $post ) {
	?>
	<p id="bdb-book-reviews-message"><?php _e( 'Below is a list of book reviews connected to this post. Deleting a review will delete it from your review archive and disassociate it with this post.', 'book-database' ); ?></p>

	<table id="bdb-post-reviews-table" class="wp-list-table widefat fixed posts" data-post-id="<?php echo esc_attr( $post->ID ); ?>" data-user-id="<?php echo esc_attr( get_current_user_id() ); ?>">
		<thead>
		<tr>
			<th class="column-primary"><?php _e( 'ID', 'book-database' ); ?></th>
			<th><?php _e( 'Book', 'book-database' ); ?></th>
			<th><?php _e( 'Rating', 'book-database' ); ?></th>
			<th><?php _e( 'Shortcode', 'book-database' ); ?></th>
			<th><?php _e( 'Remove', 'book-database' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td colspan="5"><?php _e( 'Loading...', 'book-database' ); ?></td>
		</tr>
		</tbody>
	</table>

	<p>
		<button id="bdb-associated-review-post" class="button button-secondary"><?php _e( 'Add Review', 'book-database' ); ?></button>
	</p>

	<div id="bdb-search-book-fields" style="display: none;">
		<p><?php _e( 'Search for a book to review.', 'book-database' ); ?></p>

		<p>
			<label for="bdb-search-book-title-author" class="screen-reader-text"><?php _e( 'Enter a book title or author name', 'book-database' ); ?></label>
			<input type="text" id="bdb-search-book-title-author" class="regular-text" placeholder="<?php esc_attr_e( 'Book title or author name', 'book-database' ); ?>">

			<label for="bdb-search-book-type" class="screen-reader-text"><?php _e( 'Choose a field to search by', 'book-database' ); ?></label>
			<select id="bdb-search-book-type">
				<option value="title" selected="selected"><?php esc_html_e( 'Title', 'book-database' ); ?></option>
				<option value="author"><?php esc_html_e( 'Author', 'book-database' ); ?></option>
			</select>

			<button type="button" class="button"><?php esc_html_e( 'Search for Book', 'book-database' ); ?></button>
		</p>
	</div>

	<div id="bdb-book-search-results"></div>

	<div id="bdb-add-review-fields" style="display: none;">
		<div id="bdb-add-review-reading-log-fields">
			<p>
				<label for="bdb-review-reading-log"><?php _e( 'Associated reading entry', 'book-database' ); ?></label> <br>
				<select id="bdb-review-reading-log">
					<option value=""><?php _e( 'None', 'book-database' ); ?></option>
				</select>
			</p>
		</div>

		<button type="button" id="bdb-add-review" class="button"><?php esc_html_e( 'Create Review', 'book-database' ); ?></button>
	</div>

	<div id="bdb-post-reviews-errors"></div>
	<?php
}

/**
 * Load templates
 */
function load_post_meta_templates() {

	if ( ! user_can_edit_books() ) {
		return;
	}

	$screen = get_current_screen();

	$hooks = array(
		'edit.php',
		'post.php',
		'post-new.php',
		'index.php'
	);

	if ( ! in_array( $screen->parent_file, $hooks ) ) {
		return;
	}

	$templates = array(
		'table-post-reviews-row',
		'table-post-reviews-row-empty'
	);

	foreach ( $templates as $template ) {
		if ( file_exists( BDB_DIR . 'includes/admin/posts/templates/tmpl-' . $template . '.php' ) ) {
			?>
			<script type="text/html" id="tmpl-bdb-<?php echo esc_attr( $template ); ?>">
				<?php require_once BDB_DIR . 'includes/admin/posts/templates/tmpl-' . $template . '.php'; ?>
			</script>
			<?php
		}
	}

}

add_action( 'admin_footer', __NAMESPACE__ . '\load_post_meta_templates' );