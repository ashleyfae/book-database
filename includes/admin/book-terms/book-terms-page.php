<?php
/**
 * Admin Book Terms Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Render the book terms page
 */
function render_book_terms_page() {

	$view = ! empty( $_GET['view'] ) ? urldecode( $_GET['view'] ) : '';

	switch ( $view ) {

		case 'edit' :
			require_once BDB_DIR . 'includes/admin/book-terms/edit-book-term.php';
			break;

		default :
			require_once BDB_DIR . 'includes/admin/book-terms/class-book-terms-list-table.php';

			$list_table = new Book_Terms_List_Table();
			$list_table->prepare_items();

			$taxonomies  = get_book_taxonomies( array( 'orderby' => 'name', 'order' => 'ASC' ) );
			$current_tax = $_GET['status'] ?? '';
			?>
			<div class="wrap">
				<h1>
					<?php esc_html_e( 'Book Terms', 'book-database' ); ?>
				</h1>

				<div id="col-container" class="wp-clearfix">

					<div id="col-left">
						<div class="col-wrap">
							<div class="form-wrap">
								<h2><?php _e( 'Add New Term', 'book-database' ); ?></h2>
								<form id="bdb-add-book-term" method="POST" action="<?php echo esc_url( get_book_terms_admin_page_url() ); ?>">
									<div class="form-field form-required">
										<label for="bdb-book-term-name"><?php _e( 'Name', 'book-database' ); ?></label>
										<input type="text" id="bdb-book-term-name" name="name" size="40" aria-required="true">
									</div>
									<div class="form-field">
										<label for="bdb-book-term-slug"><?php _e( 'Slug', 'book-database' ); ?></label>
										<input type="text" id="bdb-book-term-slug" name="slug" size="40">
										<p class="description"><?php _e( 'The "slug" is the URL-friendly version of a name. It is usually all lowercase and contains only letters, numbers, and hyphens. Leave blank to auto generate.', 'book-database' ); ?></p>
									</div>
									<div class="form-field">
										<label for="bdb-book-term-taxonomy"><?php _e( 'Taxonomy', 'book-database' ); ?></label>
										<select id="bdb-book-term-taxonomy" name="taxonomy">
											<?php foreach ( $taxonomies as $taxonomy ) : ?>
												<option value="<?php echo esc_attr( $taxonomy->get_slug() ); ?>" <?php selected( $current_tax, $taxonomy->get_slug() ); ?>><?php echo esc_html( $taxonomy->get_name() ); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<div class="form-field">
										<label for="bdb-book-term-description"><?php _e( 'Description', 'book-database' ); ?></label>
										<textarea id="bdb-book-term-slug" name="description" rows="5" cols="50" class="large-text"></textarea>
									</div>
									<?php
									do_action( 'book-database/terms/add-term-fields' );
									wp_nonce_field( 'bdb_add_book_term', 'bdb_add_book_term_nonce' );
									?>
									<p class="submit">
										<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add New Term', 'book-database' ); ?>">
									</p>
								</form>
							</div>
						</div>
					</div>

					<div id="col-right">
						<div class="col-wrap">
							<form id="bdb-book-terms-filter" method="GET" action="<?php echo esc_url( get_book_terms_admin_page_url() ); ?>">
								<input type="hidden" name="page" value="bdb-terms"/>
								<?php
								$list_table->search_box( __( 'Search terms', 'book-database' ), 'bdb_search_authors' );
								$list_table->views();
								$list_table->display();

								if ( ! empty( $_GET['status'] ) ) {
									?>
									<input type="hidden" name="status" value="<?php echo esc_attr( urldecode( $_GET['status'] ) ); ?>">
									<?php
								}
								?>
							</form>
						</div>
					</div>

				</div>
			</div>
			<?php
			break;
	}

}