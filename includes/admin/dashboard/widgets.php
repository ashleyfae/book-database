<?php
/**
 * Dashboard Widgets
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

/**
 * Register dashboard widgets
 *
 * @since 1.0
 * @return void
 */
function bdb_register_dashboard_widgets() {

	// Currently Reading
	wp_add_dashboard_widget( 'bdb_currently_reading', __( 'Currently Reading', 'book-database' ), 'bdb_render_currently_reading_dashboard_widget' );

}

add_action( 'wp_dashboard_setup', 'bdb_register_dashboard_widgets' );

/**
 * Render "Currently Reading" dashboard widget.
 *
 * @todo  Admin styles
 * @todo  Ajax update progress
 * @todo  Percentage inside progress bar?
 *
 * @since 1.0
 * @return void
 */
function bdb_render_currently_reading_dashboard_widget() {

	global $wpdb;

	$log_table = book_database()->reading_log->table_name;

	$query = "SELECT * from {$log_table}
		WHERE date_started IS NOT NULL
		AND date_finished IS NULL
		ORDER BY date_started DESC";

	$logs = $wpdb->get_results( $query );

	if ( empty( $logs ) ) {
		echo '<p>' . __( 'You\'re not reading any books!', 'book-database' ) . '</p>';

		return;
	}
	?>
	<ul class="bdb-currently-reading-widget-list">
		<?php
		foreach ( $logs as $log ) {
			$book = new BDB_Book( $log->book_id );
			?>
			<li>
				<?php
				$cover = $book->get_cover( 'medium' );
				if ( ! empty( $cover ) ) {
					echo '<a href="' . esc_url( bdb_get_admin_page_edit_book( $book->ID ) ) . '">' . $cover . '</a>';
				}
				?>
				<p class="bdb-currently-reading-book-title">
					<a href="<?php echo esc_url( bdb_get_admin_page_edit_book( $book->ID ) ); ?>"><?php printf( '%s by %s', $book->get_title(), $book->get_author_names() ); ?></a>
				</p>
				<div class="bdb-currently-reading-progress">
					<div class="bdb-currently-reading-progress-bar" style="width: <?php echo absint( $log->complete ); ?>%"></div>
					<span class="bdb-currently-reading-progress-number"<?php echo $log->complete >= 60 ? ' style="color: white"' : ''; ?>>
						<?php printf( '%d%%', absint( $log->complete ) ); ?>
					</span>
				</div>
				<button class="bdb-currently-reading-widget-update-progress button" data-log="<?php echo esc_attr( $log->ID ); ?>"><?php _e( 'Update', 'book-database' ); ?></button>
				<button class="bdb-currently-reading-widget-finish-book button" data-log="<?php echo esc_attr( $log->ID ); ?>"><?php _e( 'Finished', 'book-database' ); ?></button>
			</li>
			<?php
		}
		?>
	</ul>
	<?php

}