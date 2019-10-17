<?php
/**
 * Admin Edit Series Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$series = ! empty( $_GET['series_id'] ) ? get_book_series_by( 'id', absint( $_GET['series_id'] ) ) : false;

if ( ! empty( $_GET['view'] ) && 'edit' === $_GET['view'] && empty( $series ) ) {
	wp_die( __( 'Invalid series ID.', 'book-database' ) );
}
?>
<div class="wrap">
	<h1><?php echo ! empty( $series ) ? __( 'Edit Series', 'book-database' ) : __( 'Add New Series', 'book-database' ); ?></h1>

	<form id="bdb-edit-series" method="POST" action="">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="bdb-series-name"><?php _e( 'Name', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="text" id="bdb-series-name" class="regular-text" name="name" value="<?php echo ! empty( $series ) ? esc_attr( $series->get_name() ) : ''; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-series-slug"><?php _e( 'Slug', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="text" id="bdb-series-slug" class="regular-text" name="slug" value="<?php echo ! empty( $series ) ? esc_attr( $series->get_slug() ) : ''; ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-series-description"><?php _e( 'Description', 'book-database' ); ?></label>
				</th>
				<td>
					<textarea id="bdb-series-description" class="large-text" rows="5" name="description"><?php echo ! empty( $series ) ? esc_textarea( $series->get_description() ) : ''; ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bdb-series-number-books"><?php _e( 'Series Length', 'book-database' ); ?></label>
				</th>
				<td>
					<input type="number" id="bdb-series-number-books" class="regular-text" name="number_books" value="<?php echo ! empty( $series ) ? esc_attr( $series->get_number_books() ) : ''; ?>">
					<p class="description"><?php _e( 'The intended length of the series.', 'book-database' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>

		<?php
		if ( $series instanceof Series ) {
			?>
			<input type="hidden" name="series_id" value="<?php echo esc_attr( $series->get_id() ); ?>">
			<?php
			wp_nonce_field( 'bdb_update_series', 'bdb_update_series_nonce' );
			submit_button( __( 'Update Series', 'book-database' ) );
		} else {
			wp_nonce_field( 'bdb_add_series', 'bdb_add_series_nonce' );
			submit_button( __( 'Add Series', 'book-database' ) );
		}
		?>
	</form>
</div>
