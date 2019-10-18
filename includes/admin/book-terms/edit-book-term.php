<?php
/**
 * Admin Edit Book Term Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$term = ! empty( $_GET['term_id'] ) ? get_book_term( absint( $_GET['term_id'] ) ) : false;

if ( empty( $term ) ) {
	wp_die( __( 'Invalid term ID.', 'book-database' ) );
}
?>
<div class="wrap">
	<h1><?php _e( 'Edit Term', 'book-database' ); ?></h1>

	<p>
		<a href="<?php echo esc_url( get_book_terms_admin_page_url( array( 'status' => $term->get_taxonomy() ) ) ); ?>"><?php _e( '&laquo; Back to List', 'book-database' ); ?></a>
	</p>

	<form id="bdb-edit-book-term" method="POST" action="">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="bdb-book-term-name"><?php _e( 'Name', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="text" id="bdb-book-term-name" class="regular-text" name="name" value="<?php echo esc_attr( $term->get_name() ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-book-term-slug"><?php _e( 'Slug', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="text" id="bdb-book-term-slug" class="regular-text" name="slug" value="<?php echo esc_attr( $term->get_slug() ) ?>">
					<p class="description"><?php _e( 'The "slug" is the URL-friendly version of a name. It is usually all lowercase and contains only letters, numbers, and hyphens. Leave blank to auto generate.', 'book-database' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-book-term-description"><?php _e( 'Description', 'book-database' ); ?></label>
				</th>
				<td>
					<textarea id="bdb-book-term-description" class="large-text" rows="5" name="description"><?php echo esc_textarea( $term->get_description() ); ?></textarea>
				</td>
			</tr>
			</tbody>
		</table>


		<input type="hidden" name="term_id" value="<?php echo esc_attr( $term->get_id() ); ?>">
		<?php
		wp_nonce_field( 'bdb_update_book_term', 'bdb_update_book_term_nonce' );
		submit_button( __( 'Update Term', 'book-database' ) );
		?>
	</form>
</div>
