<?php
/**
 * Analytics Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Get available analytics tabs
 *
 * @return array
 */
function get_analytics_tabs() {
	return array(
		'overview' => array(
			'title'    => __( 'Overview', 'book-database' ),
			'dashicon' => 'dashboard'
		),
		'books'    => array(
			'title'    => __( 'Books', 'book-database' ),
			'dashicon' => 'book'
		),
		'reading'  => array(
			'title'    => __( 'Reading', 'book-database' ),
			'dashicon' => 'book'
		),
		'ratings'  => array(
			'title'    => __( 'Ratings', 'book-database' ),
			'dashicon' => 'star-filled'
		),
		'editions' => array(
			'title'    => __( 'Editions', 'book-database' ),
			'dashicon' => 'admin-page'
		),
		'reviews'  => array(
			'title'    => __( 'Reviews', 'book-database' ),
			'dashicon' => 'welcome-write-blog'
		),
		'terms'    => array(
			'title'    => __( 'Terms', 'book-database' ),
			'dashicon' => 'tag'
		),
	);
}

/**
 * Get the analytics admin page URL.
 *
 * @param array $args Query args to append to the URL.
 *
 * @return string
 */
function get_analytics_admin_page_url( $args = array() ) {

	$sanitized_args = array();

	foreach ( $args as $key => $value ) {
		$sanitized_args[ sanitize_key( $key ) ] = urlencode( $value );
	}

	return add_query_arg( $sanitized_args, admin_url( 'admin.php?page=bdb-analytics' ) );

}

/**
 * Render the analytics page
 */
function render_analytics_page() {

	$view = ! empty( $_GET['view'] ) ? urldecode( $_GET['view'] ) : 'overview';
	?>
	<div id="bdb-book-analytics-wrap" class="wrap">
		<h1><?php _e( 'Reading &amp; Review Analytics', 'book-database' ); ?></h1>

		<form id="bdb-analytics-date-range" method="GET">
			<label for="bdb-analytics-date-range-select" class="screen-reader-text"><?php _e( 'Date Range', 'book-database' ); ?></label>
			<select id="bdb-analytics-date-range-select" name="range">
				<?php foreach ( Analytics\get_dates_filter_options() as $filter_key => $filter_value ) : ?>
					<option value="<?php echo esc_attr( $filter_key ); ?>" <?php selected( $filter_key, Analytics\get_current_date_filter()['option'] ); ?>>
						<?php echo esc_html( $filter_value ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<?php wp_nonce_field( 'bdb_set_analytics_date_filter', 'bdb_analytics_date_filter_nonce' ); ?>
			<input type="hidden" name="bdb_analytics_view" value="<?php echo esc_attr( $view ); ?>">
			<button type="submit" id="bdb-analytics-set-date-range" class="button"><?php esc_html_e( 'Filter', 'book-database' ); ?></button>
		</form>

		<div class="bdb-panels-wrap">
			<ul class="bdb-tabs">
				<?php foreach ( get_analytics_tabs() as $tab_key => $tab ) : ?>
					<li<?php echo $tab_key === $view ? ' class="bdb-tab-active"' : ''; ?>>
						<a href="<?php echo esc_url( get_analytics_admin_page_url( array( 'view' => urlencode( $tab_key ) ) ) ); ?>">
							<?php if ( ! empty( $tab['dashicon'] ) ) : ?>
								<i class="dashicons dashicons-<?php echo sanitize_html_class( $tab['dashicon'] ); ?>"></i>
							<?php endif; ?>
							<?php echo esc_html( $tab['title'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="bdb-panels">
				<div id="bdb-dataset-wrap-<?php echo sanitize_html_class( $view ); ?>" class="bdb-panel bdb-panel-active">
					<?php do_action( 'book-database/analytics/' . $view ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php

}