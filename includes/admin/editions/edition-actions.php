<?php
/**
 * Admin Edition Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * @param Book|false $book
 */
function editions_table( $book ) {

	// Bail on new books.
	if ( empty( $book ) ) {
		return;
	}

	$source_tax = get_book_taxonomy_by( 'slug', 'source' );
	?>
	<div id="bdb-book-editions-list" class="postbox">
		<h2><?php _e( 'Owned Editions', 'book-database' ); ?></h2>
		<div class="inside">
			<table class="wp-list-table widefat fixed posts">
				<thead>
				<tr>
					<th class="column-primary"><?php _e( 'ISBN', 'book-database' ); ?></th>
					<th><?php _e( 'Format', 'book-database' ); ?></th>
					<th><?php _e( 'Date Acquired', 'book-database' ); ?></th>
					<th><?php _e( 'Source', 'book-database' ); ?></th>
					<th><?php _e( 'Signed', 'book-database' ); ?></th>
					<th><?php _e( 'Actions', 'book-database' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td colspan="6"><?php _e( 'Loading...', 'book-database' ); ?></td>
				</tr>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="6">
						<button type="button" id="bdb-add-edition" class="button"><?php _e( 'Add Edition', 'book-database' ); ?></button>
					</td>
				</tr>
				</tfoot>
			</table>

			<div id="bdb-new-edition-fields">
				<div class="bdb-meta-row">
					<label for="bdb-new-edition-isbn"><?php _e( 'ISBN or ASIN', 'book-database' ); ?></label>
					<div class="bdb-meta-value">
						<input type="text" id="bdb-new-edition-isbn" class="regular-text">
					</div>
				</div>
				<div class="bdb-meta-row">
					<label for="bdb-new-edition-format"><?php _e( 'Format', 'book-database' ); ?></label>
					<div class="bdb-meta-value">
						<select id="bdb-new-edition-format">
							<?php foreach ( get_book_formats() as $format_key => $format_name ) : ?>
								<option value="<?php echo esc_attr( $format_key ); ?>"><?php echo esc_html( $format_name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="bdb-meta-row">
					<label for="bdb-new-edition-date-acquired"><?php _e( 'Date Acquired', 'book-database' ); ?></label>
					<div class="bdb-meta-value">
						<input type="text" id="bdb-new-edition-date-acquired" value="<?php echo esc_attr( format_date( current_time( 'mysql', true ) ) ); ?>" class="regular-text">
						<p class="description"><?php _e( 'Date you acquired the book.', 'book-database' ); ?></p>
					</div>
				</div>
				<div class="bdb-meta-row">
					<label for="bdb-new-edition-source"><?php _e( 'Source', 'book-database' ); ?></label>
					<div class="bdb-meta-value">
						<?php
						if ( false !== $source_tax ) {
							book_database()->get_html()->taxonomy_field( $source_tax, array( 'id' => 'edition' ) );
						} else {
							echo '&ndash;';
						}
						?>
					</div>
				</div>
				<div class="bdb-meta-row">
					<label>
						<?php _e( 'Signed', 'book-database' ); ?>
					</label>
					<div class="bdb-meta-value">
						<input type="checkbox" id="bdb-new-edition-signed" value="1">
						<label for="bdb-new-edition-signed">
							<?php _e( 'Check on if the book is signed', 'book-database' ); ?>
						</label>
					</div>
				</div>
				<div class="bdb-meta-row">
					<button id="bdb-submit-new-edition" class="button-primary"><?php _e( 'Add Edition', 'book-database' ); ?></button>
				</div>
			</div>

			<div id="bdb-editions-errors" class="bdb-notice bdb-notice-error" style="display: none;"></div>
		</div>
	</div>
	<?php

}

add_action( 'book-database/book-edit/after-information-fields', __NAMESPACE__ . '\editions_table' );

/**
 * Load edition templates
 */
function load_edition_templates() {

	global $bdb_admin_pages;

	$screen = get_current_screen();

	if ( $screen->id !== $bdb_admin_pages['books'] ) {
		return;
	}

	$templates = array( 'row', 'row-empty' );

	foreach ( $templates as $template ) {
		?>
		<script type="text/html" id="tmpl-bdb-editions-table-<?php echo esc_attr( $template ); ?>">
			<?php require_once BDB_DIR . 'includes/admin/editions/templates/tmpl-editions-table-' . $template . '.php'; ?>
		</script>
		<?php
	}

}

add_action( 'admin_footer', __NAMESPACE__ . '\load_edition_templates' );