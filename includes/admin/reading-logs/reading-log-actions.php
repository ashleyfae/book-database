<?php
/**
 * Admin Reading Log Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Add a table of reading logs to the Edit Book page
 *
 * @param Book|false $book
 */
function reading_logs_table( $book ) {

	// Bail on new book.
	if ( empty( $book ) ) {
		return;
	}

	$user_counts = count_users();
	?>
	<div id="bdb-book-reading-logs-list" class="postbox" data-user-id="<?php echo esc_attr( get_current_user_id() ); ?>">
		<h2>
			<?php _e( 'Reading Log', 'book-database' ); ?>
			<label for="bdb-book-reading-logs-user-filter"<?php echo $user_counts['total_users'] > 1 ? '' : ' style="display: none;"'; ?>>
				<input type="checkbox" id="bdb-book-reading-logs-user-filter" value="1" checked="checked">
				<?php _e( 'Only show my entries', 'book-database' ); ?>
			</label>
		</h2>
		<div class="inside">
			<table class="wp-list-table widefat fixed posts">
				<thead>
				<tr>
					<th class="column-primary"><?php _e( 'Date Started', 'book-database' ); ?></th>
					<th><?php _e( 'Date Finished', 'book-database' ); ?></th>
					<th><?php _e( 'Edition', 'book-database' ); ?></th>
					<th><?php _e( 'Review ID', 'book-database' ); ?></th>
					<th><?php _e( 'User ID', 'book-database' ); ?></th>
					<th><?php _e( '% Complete', 'book-database' ); ?></th>
					<th><?php _e( 'Rating', 'book-database' ); ?></th>
					<th><?php _e( 'Actions', 'book-database' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td colspan="8"><?php _e( 'Loading...', 'book-database' ); ?></td>
				</tr>
				</tbody>
				<?php if ( user_can_edit_books() ) : ?>
					<tfoot>
					<tr>
						<td colspan="8">
							<button type="button" id="bdb-add-reading-log" class="button"><?php _e( 'Add Reading Log', 'book-database' ); ?></button>
						</td>
					</tr>
					</tfoot>
				<?php endif; ?>
			</table>

			<?php if ( user_can_edit_books() ) : ?>
				<div id="bdb-new-reading-log-fields">
					<div id="bdb-new-log-edition-id-wrap" class="bdb-meta-row" style="display: none;">
						<label for="bdb-new-log-edition-id"><?php _e( 'Edition', 'book-database' ); ?></label>
						<div class="bdb-meta-value">
							<select id="bdb-new-log-edition-id" class="bdb-book-edition-list">
								<option value=""><?php _e( 'Loading...', 'book-database' ); ?></option>
							</select>
							<p class="description"><?php _e( 'Edition that you read (optional).', 'book-database' ); ?></p>
						</div>
					</div>
					<div class="bdb-meta-row">
						<label for="bdb-new-log-start-date"><?php _e( 'Start Date', 'book-database' ); ?></label>
						<div class="bdb-meta-value">
							<input type="text" id="bdb-new-log-start-date" class="bdb-datepicker" value="<?php echo esc_attr( format_date( current_time( 'mysql', true ), 'Y-m-d' ) ); ?>">
							<p class="description"><?php _e( 'Date you started reading the book.', 'book-database' ); ?></p>
						</div>
					</div>
					<div class="bdb-meta-row">
						<label for="bdb-new-log-end-date"><?php _e( 'Finish Date', 'book-database' ); ?></label>
						<div class="bdb-meta-value">
							<input type="text" id="bdb-new-log-end-date" class="bdb-datepicker">
							<p class="description"><?php _e( 'Date you finished reading the book. Leave blank if you\'re not finished.', 'book-database' ); ?></p>
						</div>
					</div>
					<div class="bdb-meta-row">
						<label for="bdb-new-log-percent-complete"><?php _e( '% Complete', 'book-database' ); ?></label>
						<div class="bdb-meta-value">
							<input type="number" id="bdb-new-log-percent-complete" class="regular-text bdb-input-has-suffix">
							<span class="bdb-input-suffix">%</span>
							<p class="description"><?php _e( 'Percentage of the book you\'ve read.', 'book-database' ); ?></p>
						</div>
					</div>
					<div class="bdb-meta-row">
						<label for="bdb-new-log-rating"><?php _e( 'Rating', 'book-database' ); ?></label>
						<div class="bdb-meta-value">
							<select id="bdb-new-log-rating">
								<option value=""><?php _e( 'None', 'book-database' ); ?></option>
								<?php foreach ( get_available_ratings() as $rating_value => $rating_label ) : ?>
									<option value="<?php echo esc_attr( $rating_value ); ?>"><?php echo esc_html( $rating_label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="bdb-meta-row">
						<button type="button" id="bdb-submit-new-reading-log" class="button-primary"><?php _e( 'Add Log', 'book-database' ); ?></button>
					</div>
				</div>
			<?php endif; ?>

			<div id="bdb-reading-logs-errors" class="bdb-notice bdb-notice-error" style="display: none;"></div>
		</div>
	</div>
	<?php

}

add_action( 'book-database/book-edit/after-information-fields', __NAMESPACE__ . '\reading_logs_table' );

/**
 * Load reading log templates
 */
function load_reading_log_templates() {

	global $bdb_admin_pages;

	$screen = get_current_screen();

	if ( $screen->id !== $bdb_admin_pages['books'] ) {
		return;
	}

	$templates = array( 'row', 'row-empty' );

	foreach ( $templates as $template ) {
		?>
		<script type="text/html" id="tmpl-bdb-reading-logs-table-<?php echo esc_attr( $template ); ?>">
			<?php require_once BDB_DIR . 'includes/admin/reading-logs/templates/tmpl-reading-logs-table-' . $template . '.php'; ?>
		</script>
		<?php
	}

}

add_action( 'admin_footer', __NAMESPACE__ . '\load_reading_log_templates' );