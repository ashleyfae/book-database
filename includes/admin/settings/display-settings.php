<?php
/**
 * Display Settings
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Render the settings page
 */
function render_settings_page() {

	$tab  = ! empty( $_GET['tab'] ) ? $_GET['tab'] : 'books';
	$base = admin_url( 'admin.php?page=bdb-settings' );
	?>
	<div id="bdb-settings-wrap" class="wrap">
		<h1><?php _e( 'Book Database Settings', 'book-database' ); ?></h1>

		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( $base ); ?>" title="<?php esc_attr_e( 'Books', 'book-database' ); ?>" class="nav-tab<?php echo $tab === 'books' ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Books', 'book-database' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'reviews', $base ) ); ?>" title="<?php esc_attr_e( 'Reviews', 'book-database' ); ?>" class="nav-tab<?php echo $tab === 'reviews' ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Reviews', 'book-database' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'misc', $base ) ); ?>" title="<?php esc_attr_e( 'Misc', 'book-database' ); ?>" class="nav-tab<?php echo $tab === 'misc' ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Misc', 'book-database' ); ?></a>
			<?php do_action( 'book-database/settings/tabs' ); ?>
		</h2>

		<div id="tab_container">
			<form method="POST" action="options.php">
				<table class="form-table">
					<?php
					switch ( $tab ) {

						case 'books' :
							?>
							<tr>
								<th scope="row"><?php _e( 'Book Layout', 'book-database' ); ?></th>
								<td>
									<div id="bdb-book-layout-builder">

										<div id="bdb-enabled-book-settings">
											<h3 class="bdb-no-sort"><?php _e( 'Enabled Fields', 'book-database' ); ?></h3>
											<div id="bdb-enabled-book-settings-inner" class="bdb-sortable bdb-sorter-enabled-column">
												<?php
												foreach ( get_enabled_book_fields() as $key => $options ) {
													format_admin_book_layout_option( $key, false );
												}
												?>
											</div>
										</div>

										<div id="bdb-available-book-settings">
											<h3 class="bdb-no-sort"><?php _e( 'Disabled Fields', 'book-database' ); ?></h3>
											<div id="bdb-available-book-settings-inner" class="bdb-sortable">
												<?php
												foreach ( get_book_fields() as $key => $options ) {
													if ( ! array_key_exists( $key, get_enabled_book_fields() ) ) {
														format_admin_book_layout_option( $key, true );
													}
												}
												?>
											</div>
										</div>

									</div>
								</td>
							</tr><!--/book layout-->
							<tr>
								<th scope="row"><?php _e( 'Book Taxonomies', 'book-database' ); ?></th>
								<td>
									<table id="bdb-book-taxonomies" class="wp-list-table widefat fixed striped">
										<thead>
										<tr>
											<th scope="col" class="column-primary"><?php _e( 'Name', 'book-database' ); ?></th>
											<th scope="col"><?php _e( 'Slug', 'book-database' ); ?></th>
											<th scope="col"><?php _e( 'Format', 'book-database' ); ?></th>
											<th scope="col"><?php _e( 'Actions', 'book-database' ); ?></th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<td colspan="4"><?php _e( 'Loading...', 'book-database' ); ?></td>
										</tr>
										</tbody>
										<tfoot>
										<tr>
											<th><?php _e( 'Name', 'book-database' ); ?></th>
											<th><?php _e( 'Slug', 'book-database' ); ?></th>
											<th><?php _e( 'Format', 'book-database' ); ?></th>
											<th><?php _e( 'Actions', 'book-database' ); ?></th>
										</tr>
										<tr id="bdb-new-book-taxonomy-fields">
											<td data-th="<?php esc_attr_e( 'Name', 'book-database' ); ?>">
												<label for="bdb-new-book-taxonomy-name" class="screen-reader-text"><?php _e( 'Enter a name for the taxonomy', 'book-database' ); ?></label>
												<input type="text" id="bdb-new-book-taxonomy-name">
											</td>
											<td data-th="<?php esc_attr_e( 'Slug', 'book-database' ); ?>">
												<label for="bdb-new-book-taxonomy-slug" class="screen-reader-text"><?php _e( 'Enter a unique slug for the taxonomy', 'book-database' ); ?></label>
												<input type="text" id="bdb-new-book-taxonomy-slug">
											</td>
											<td data-th="<?php esc_attr_e( 'Format', 'book-database' ); ?>">
												<label for="bdb-new-book-taxonomy-format" class="screen-reader-text"><?php _e( 'Select a format for the taxonomy terms', 'book-database' ); ?></label>
												<select id="bdb-new-book-taxonomy-format">
													<option value="text"><?php _e( 'Text', 'book-database' ); ?></option>
													<option value="checkbox"><?php _e( 'Checkbox', 'book-database' ); ?></option>
												</select>
											</td>
											<td data-th="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
												<button class="button-primary"><?php _e( 'Add', 'book-database' ); ?></button>
											</td>
										</tr>
										</tfoot>
									</table>
									<div id="bdb-book-taxonomies-errors" class="bdb-notice bdb-notice-error" style="display: none;"></div>
								</td>
							</tr>
							<?php
							break;

						case 'reviews' :
							?>
							<tr>
								<th scope="row">
									<label for="bdb-settings-reviews-page"><?php _e( 'Reviews Page', 'book-database' ); ?></label>
								</th>
								<td>
									<?php
									$reviews_page = bdb_get_option( 'reviews_page' );
									$pages        = get_pages();
									?>
									<select id="bdb-settings-reviews-page" name="bdb_settings[reviews_page]">
										<option value="" <?php selected( empty( $reviews_page ) ); ?>><?php _e( 'Select a Page', 'book-database' ); ?></option>
										<?php foreach ( $pages as $page ) : ?>
											<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $page->ID, $reviews_page ); ?>><?php echo esc_html( $page->post_title ); ?></option>
										<?php endforeach; ?>
									</select>
									<?php if ( ! empty( $reviews_page ) ) : ?>
										<a href="<?php echo esc_url( get_permalink( $reviews_page ) ); ?>" class="button"><?php _e( 'View Page', 'book-database' ); ?></a>
										<a href="<?php echo esc_url( add_query_arg( 'post', urlencode( $reviews_page ), admin_url( 'post.php?action=edit' ) ) ); ?>" class="button"><?php _e( 'Edit Page', 'book-database' ); ?></a>
									<?php endif; ?>
									<p class="description"><?php printf( __( 'Page containing the %s shortcode.', 'book-database' ), '<code>[book-reviews]</code>' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="bdb-settings-sync-review-publish-date"><?php _e( 'Sync Review Publish Date', 'book-database' ); ?></label>
								</th>
								<td>
									<input type="hidden" name="bdb_settings[sync_published_date]" value="0">
									<input type="checkbox" id="bdb-settings-sync-review-publish-date" name="bdb_settings[sync_published_date]" value="1" <?php checked( bdb_get_option( 'sync_published_date' ), 1 ); ?>>
									<span class="description"><?php _e( 'If checked, reviews connected to a post will have their publish date synced to the post\'s publish date.', 'book-database' ); ?></span>
								</td>
							</tr>
							<?php
							break;

						case 'misc' :
							?>
							<tr>
								<th scope="row">
									<label for="bdb-settings-delete-uninstall"><?php _e( 'Delete on Uninstall', 'book-database' ); ?></label>
								</th>
								<td>
									<input type="hidden" name="bdb_settings[delete_on_uninstall]" value="0">
									<input type="checkbox" id="bdb-settings-delete-uninstall" name="bdb_settings[delete_on_uninstall]" value="1" <?php checked( bdb_get_option( 'delete_on_uninstall' ), 1 ); ?>>
									<span class="description"><?php _e( 'Check this box if you would like Book Database to completely remove all of its data when the plugin is deleted. This will permanently remove all book, review, and reading log information.', 'book-database' ); ?></span>
								</td>
							</tr>
							<?php
							break;

						default :
							do_action( 'book-database/settings/tab/' . $tab );
							break;

					}
					?>
				</table>

				<div class="bdb-settings-buttons">
					<?php
					settings_fields( 'bdb_settings' );
					submit_button();
					?>
				</div>
			</form>
		</div>
	</div>
	<?php

}