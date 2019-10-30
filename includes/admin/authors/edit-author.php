<?php
/**
 * Admin Add/Edit Author Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$author = ! empty( $_GET['author_id'] ) ? get_book_author( absint( $_GET['author_id'] ) ) : false;

if ( ! empty( $_GET['view'] ) && 'edit' === $_GET['view'] && empty( $author ) ) {
	wp_die( __( 'Invalid author ID.', 'book-database' ) );
}
?>
<div class="wrap">
	<h1><?php echo ! empty( $author ) ? __( 'Edit Author', 'book-database' ) : __( 'Add New Author', 'book-database' ); ?></h1>

	<form id="bdb-edit-author" method="POST" action="">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="bdb-author-name"><?php _e( 'Name', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="text" id="bdb-author-name" class="regular-text" name="name" value="<?php echo ! empty( $author ) ? esc_attr( $author->get_name() ) : ''; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-author-slug"><?php _e( 'Slug', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="text" id="bdb-author-slug" class="regular-text" name="slug" value="<?php echo ! empty( $author ) ? esc_attr( $author->get_slug() ) : ''; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-author-description"><?php _e( 'Description', 'book-database' ); ?></label>
				</th>
				<td>
					<textarea id="bdb-author-description" class="large-text" rows="5" name="description"><?php echo ! empty( $author ) ? esc_textarea( $author->get_description() ) : ''; ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php _e( 'Photo', 'book-database' ); ?>
				</th>
				<td>
					<?php
					$image_id  = ! empty( $author ) ? $author->get_image_id() : 0;
					$image_url = ! empty( $author ) ? $author->get_image_url( 'large' ) : '';
					?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Author photo', 'book-database' ); ?>" id="bdb-author-image" style="<?php echo empty( $image_url ) ? 'display: none;' : ''; ?>">

					<div class="bdb-author-image-fields" data-image="#bdb-author-image" data-image-id="#bdb-author-image-id" data-image-size="large">
						<button class="bdb-upload-image button"><?php esc_html_e( 'Upload Image', 'book-database' ); ?></button>
						<button class="bdb-remove-image button" style="<?php echo empty( $image_id ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'book-database' ); ?></button>
					</div>
					<input type="hidden" id="bdb-author-image-id" name="image_id" value="<?php echo esc_attr( $image_id ); ?>">
				</td>
			</tr>
			</tbody>
		</table>

		<?php
		if ( $author instanceof Author ) {
			?>
			<input type="hidden" name="author_id" value="<?php echo esc_attr( $author->get_id() ); ?>">
			<?php
			wp_nonce_field( 'bdb_update_author', 'bdb_update_author_nonce' );
			submit_button( __( 'Update Author', 'book-database' ) );
		} else {
			wp_nonce_field( 'bdb_add_author', 'bdb_add_author_nonce' );
			submit_button( __( 'Add Author', 'book-database' ) );
		}
		?>
	</form>
</div>
