<?php
/**
 * Dashboard Widgets
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Admin\Dashboard;

use Book_Database\Book;
use function Book_Database\book_database;
use function Book_Database\get_available_ratings;
use function Book_Database\get_book;
use function Book_Database\get_books_admin_page_url;
use function Book_Database\user_can_edit_books;

/**
 * Register dashboard widgets
 *
 * @since 1.0
 */
function register_widgets() {

	// Currently Reading
	wp_add_dashboard_widget( 'bdb_currently_reading', __( 'Currently Reading', 'book-database' ), __NAMESPACE__ . '\render_currently_reading' );

}

add_action( 'wp_dashboard_setup', __NAMESPACE__ . '\register_widgets' );

/**
 * Render "Currently Reading" dashboard widget
 *
 * @since 1.0
 */
function render_currently_reading() {

	global $wpdb;

	$tbl_logs = book_database()->get_table( 'reading_log' )->get_table_name();

	$query = $wpdb->prepare(
		"SELECT * from {$tbl_logs}
		WHERE date_started IS NOT NULL
		AND date_finished IS NULL
		AND user_id = %d
		ORDER BY date_started DESC",
		get_current_user_id()
	);

	$logs = $wpdb->get_results( $query );

	if ( empty( $logs ) ) {
		echo '<p>' . __( 'You\'re not reading any books!', 'book-database' ) . '</p>';

		return;
	}
	?>
	<ul class="bdb-currently-reading-widget-list">
		<?php
		foreach ( $logs as $log ) {
			$book = get_book( absint( $log->book_id ) );
			if ( ! $book instanceof Book ) {
				continue;
			}

			$edit_book_url = get_books_admin_page_url( array( 'view' => 'edit', 'book_id' => $book->get_id() ) );
			?>
			<li data-log-id="<?php echo esc_attr( $log->id ); ?>" data-now="<?php echo esc_attr( date( 'Y-m-d H:i:s' ) ); ?>">
				<?php
				$cover = $book->get_cover( 'medium' );
				if ( ! empty( $cover ) ) {
					echo '<a href="' . esc_url( $edit_book_url ) . '">' . $cover . '</a>';
				}
				?>
				<p class="bdb-currently-reading-book-title">
					<a href="<?php echo esc_url( $edit_book_url ); ?>"><?php printf( '%s by %s', $book->get_title(), $book->get_author_names( true ) ); ?></a>
				</p>
				<div class="bdb-currently-reading-data">
					<div class="bdb-currently-reading-progress">
						<div class="bdb-currently-reading-progress-bar" style="width: <?php echo absint( $log->percentage_complete * 100 ); ?>%"></div>
						<span class="bdb-currently-reading-progress-number"<?php echo $log->percentage_complete >= 0.6 ? ' style="color: white"' : ''; ?>>
							<?php printf( '%d%%', absint( $log->percentage_complete * 100 ) ); ?>
						</span>
					</div>
					<?php if ( user_can_edit_books() ) : ?>
						<button type="button" class="bdb-currently-reading-widget-update-progress button"><?php _e( 'Update', 'book-database' ); ?></button>
						<button type="button" class="bdb-currently-reading-widget-finish-book button"><?php _e( 'Finished', 'book-database' ); ?></button>
						<button type="button" class="bdb-currently-reading-widget-dnf-book button"><?php _e( 'DNF', 'book-database' ); ?></button>
					<?php endif; ?>
				</div>
				<?php if ( user_can_edit_books() ) : ?>
					<div class="bdb-currently-reading-set-progress-wrap" style="display: none;">
						<p class="bdb-currently-reading-progress-unit-choices">
							<a href="#" class="bdb-currently-reading-progress-unit-selected" data-unit="percentage"><?php _e( 'Percentage', 'book-database' ); ?></a>
							&nbsp;|&nbsp;
							<a href="#" data-unit="page"><?php _e( 'Page', 'book-database' ); ?></a>
						</p>

						<p class="bdb-currently-reading-unit-percentage-wrap">
							<label for="bdb-currently-reading-percentage-complete-<?php echo esc_attr( $log->id ); ?>" class="screen-reader-text"><?php _e( 'Enter the percentage you\'ve read', 'book-database' ); ?></label>
							<span>
								<input type="number" id="bdb-currently-reading-percentage-complete-<?php echo esc_attr( $log->id ); ?>" class="bdb-currently-reading-unit-percentage bdb-input-has-suffix" value="<?php echo esc_attr( round( $log->percentage_complete * 100 ) ); ?>">
								<span class="bdb-input-suffix">%</span>
							</span>
						</p>

						<p class="bdb-currently-reading-unit-pages-wrap" style="display: none;">
							<label for="bdb-currently-reading-page-complete-<?php echo esc_attr( $log->id ); ?>" class="screen-reader-text"><?php _e( 'Enter your current page number', 'book-database' ); ?></label>
							<span>
								<input type="number" id="bdb-currently-reading-page-complete-<?php echo esc_attr( $log->id ); ?>" class="bdb-currently-reading-unit-page bdb-input-has-suffix" value="<?php echo esc_attr( round( $log->percentage_complete * $book->get_pages() ) ); ?>" data-max="<?php echo esc_attr( $book->get_pages() ); ?>">
								<span class="bdb-input-suffix"><?php printf( __( 'of %d', 'book-database' ), $book->get_pages() ); ?></span>
							</span>
						</p>

						<button type="button" class="bdb-currently-reading-widget-save-progress button"><?php _e( 'Save', 'book-database' ); ?></button>
					</div>

					<div class="bdb-currently-reading-rate-book" style="display: none;">
						<p>
							<label for="bdb-rating-<?php echo esc_attr( $log->id ); ?>"><?php _e( 'Rate the book (optional)', 'book-database' ); ?></label>
						</p>
						<select id="bdb-rating-<?php echo esc_attr( $log->id ); ?>" class="bdb-currently-reading-rating">
							<?php foreach ( get_available_ratings() as $rating_key => $rating_label ) : ?>
								<option value="<?php echo esc_attr( $rating_key ); ?>"><?php echo esc_html( $rating_label ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="bdb-currently-reading-widget-set-rating button"><?php _e( 'Set Rating', 'book-database' ); ?></button>
					</div>
				<?php endif; ?>
			</li>
			<?php
		}
		?>
	</ul>
	<?php

}
