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
											<td data-colname="<?php esc_attr_e( 'Name', 'book-database' ); ?>">
												<label for="bdb-new-book-taxonomy-name" class="screen-reader-text"><?php _e( 'Enter a name for the taxonomy', 'book-database' ); ?></label>
												<input type="text" id="bdb-new-book-taxonomy-name">
												<button type="button" class="toggle-row">
													<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
												</button>
											</td>
											<td data-colname="<?php esc_attr_e( 'Slug', 'book-database' ); ?>">
												<label for="bdb-new-book-taxonomy-slug" class="screen-reader-text"><?php _e( 'Enter a unique slug for the taxonomy', 'book-database' ); ?></label>
												<input type="text" id="bdb-new-book-taxonomy-slug">
											</td>
											<td data-colname="<?php esc_attr_e( 'Format', 'book-database' ); ?>">
												<label for="bdb-new-book-taxonomy-format" class="screen-reader-text"><?php _e( 'Select a format for the taxonomy terms', 'book-database' ); ?></label>
												<select id="bdb-new-book-taxonomy-format">
													<option value="text"><?php _e( 'Text', 'book-database' ); ?></option>
													<option value="checkbox"><?php _e( 'Checkbox', 'book-database' ); ?></option>
												</select>
											</td>
											<td data-colname="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
												<button type="button" class="button-primary"><?php _e( 'Add', 'book-database' ); ?></button>
											</td>
										</tr>
										</tfoot>
									</table>
									<div id="bdb-book-taxonomies-errors" class="bdb-notice bdb-notice-error" style="display: none;"></div>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Retailers', 'book-database' ); ?></th>
								<td>
									<table id="bdb-retailers" class="wp-list-table widefat fixed striped">
										<thead>
										<tr>
											<th scope="col" class="column-primary"><?php _e( 'Name', 'book-database' ); ?></th>
											<th scope="col"><?php _e( 'Book Info Template', 'book-database' ); ?></th>
											<th scope="col"><?php _e( 'Actions', 'book-database' ); ?></th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<td colspan="2"><?php _e( 'Loading...', 'book-database' ); ?></td>
										</tr>
										</tbody>
										<tfoot>
										<tr>
											<th><?php _e( 'Name', 'book-database' ); ?></th>
											<th scope="col"><?php _e( 'Book Info Template', 'book-database' ); ?></th>
											<th><?php _e( 'Actions', 'book-database' ); ?></th>
										</tr>
										<tr id="bdb-new-retailer-fields">
											<td class="column-primary" data-colname="<?php esc_attr_e( 'Name', 'book-database' ); ?>">
												<label for="bdb-new-retailer-name" class="screen-reader-text"><?php _e( 'Enter a name for the retailer', 'book-database' ); ?></label>
												<input type="text" id="bdb-new-retailer-name">
											</td>
											<td data-colname="<?php esc_attr_e( 'Book Info Template', 'book-database' ); ?>">
												<label for="bdb-new-retailer-template" class="screen-reader-text"><?php _e( 'Format the template for use in displaying book information', 'book-database' ); ?></label>
												<textarea id="bdb-new-retailer-template" class="regular-text"><?php echo esc_textarea( sprintf( '<a href="[url]" target="_blank">%s</a>', __( 'Buy the Book', 'book-database' ) ) ); ?></textarea>
											</td>
											<td data-colname="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
												<button type="button" class="button-primary"><?php _e( 'Add', 'book-database' ); ?></button>
											</td>
										</tr>
										</tfoot>
									</table>
									<div id="bdb-retailers-errors" class="bdb-notice bdb-notice-error" style="display: none;"></div>
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
									<label for="bdb-settings-rating-display"><?php _e( 'Rating Display Format', 'book-database' ); ?></label>
								</th>
								<td>
									<?php
									$rating_display = bdb_get_option( 'rating_display', 'html_stars' );
									?>
									<select id="bdb-settings-rating-display" name="bdb_settings[rating_display]">
										<option value="font_awesome" <?php selected( $rating_display, 'font_awesome' ); ?>><?php _e( 'Font Awesome Stars', 'book-database' ); ?></option>
										<option value="html_stars" <?php selected( $rating_display, 'html_stars' ); ?>><?php _e( 'HTML Stars', 'book-database' ); ?></option>
										<option value="text" <?php selected( $rating_display, 'text' ); ?>><?php _e( 'Plain Text', 'book-database' ); ?></option>
									</select>
									<p class="description"><?php _e( 'How ratings are displayed in shortcodes.', 'book-database'); ?></p>
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
									<label for="bdb-license-key"><?php _e( 'License Key', 'book-database' ); ?></label>
								</th>
								<td>
									<?php
									$license     = new License_Key();
									$license_key = $license->get_key();
									?>
									<input type="text" id="bdb-license-key" class="regular-text" name="bdb_license_key" value="<?php echo esc_attr( $license_key ); ?>">
									<?php if ( empty( $license_key ) ) : ?>
										<button type="button" id="bdb-activate-license-key" class="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bdb_activate_license_key' ) ); ?>"><?php _e( 'Activate', 'book-database' ); ?></button>
									<?php else : ?>
										<button type="button" id="bdb-deactivate-license-key" class="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bdb_deactivate_license_key' ) ); ?>"><?php _e( 'Deactivate', 'book-database' ); ?></button>
										<button type="button" id="bdb-refresh-license-key" class="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'bdb_refresh_license_key' ) ); ?>"><?php _e( 'Refresh', 'book-database' ); ?></button>
									<?php endif; ?>
									<div id="bdb-license-key-response"></div>
									<p class="description">
										<?php
										if ( empty( $license_key ) ) {
											_e( 'Enter your license key to receive automatic updates.', 'book-database' );
										} else {
											echo $license->get_status_message();
										}
										?>
									</p>
								</td>
							</tr>
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