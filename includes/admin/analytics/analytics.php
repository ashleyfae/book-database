<?php
/**
 * Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render Analytics Page
 *
 * @since 1.0.0
 * @return void
 */
function bdb_analytics_page() {

	$time = current_time( 'timestamp' );
	?>
	<div id="bookdb-review-analytics-wrap" class="wrap">
		<h1>
			<?php _e( 'Review Analytics', 'book-database' ); ?>
		</h1>

		<div id="bookdb-date-range">
			<input type="hidden" id="bookdb-start" value="-30 days">
			<input type="hidden" id="bookdb-end" value="now">
			<select id="bookdb-range">
				<option value="30-days" data-start="-30 days" data-end="now"><?php _e( 'Last 30 days', 'book-database' ); ?></option>
				<option value="this-month" data-start="<?php echo esc_attr( date( 'Y-m-1 00:00:00', $time ) ); ?>" data-end="now"><?php _e( 'This month', 'book-database' ); ?></option>
				<option value="last-month" data-start="<?php echo esc_attr( date( 'd-m-Y', strtotime( '-1 month', strtotime( date( 'Y-m-1 00:00:00', $time ) ) ) ) ); ?>" data-end="last day of last month"><?php _e( 'Last month', 'book-database' ); ?></option>
				<option value="this-year" data-start="<?php echo esc_attr( date( 'Y-1-1 00:00:00', $time ) ); ?>" data-end="now"><?php _e( 'This year', 'book-database' ); ?></option>
				<option value="last-year" data-start="<?php echo esc_attr( date( 'd-m-Y', strtotime( '-1 year', strtotime( date( 'Y-1-1 00:00:00', $time ) ) ) ) ); ?>" data-end="<?php echo esc_attr( date( 'd-m-Y', strtotime( '-1 year', strtotime( date( 'Y-12-31 00:00:00', $time ) ) ) ) ); ?>"><?php _e( 'Last year', 'book-database' ); ?></option>
				<option value="custom"><?php _e( 'Custom', 'book-database' ); ?></option>
			</select>
			<button type="button" class="button"><?php _e( 'Update', 'book-database' ); ?></button>
		</div>

		<section id="bookdb-review-analytics-metrics">

			<div class="bookdb-analytics-column">
				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Books Read', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<h2 id="number-books" class="bookdb-result"></h2>
						<p class="bottom-text" id="number-books-compare"><span></span></p>
					</div>
				</div>
				
				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Reviews', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<h2 id="number-reviews" class="bookdb-result"></h2>
						<p class="bottom-text" id="number-reviews-compare"><span></span></p>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Pages Read', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<h2 id="pages" class="bookdb-result"></h2>
						<p class="bottom-text" id="pages-compare"><span></span></p>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Average Rating', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<h2 id="avg-rating" class="bookdb-result"></h2>
						<p class="bottom-text" id="avg-rating-compare"><span></span></p>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Rating Breakdown', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<div id="rating-breakdown" class="bookdb-result"></div>
					</div>
				</div>
			</div>

			<div class="bookdb-analytics-column">
				<?php
				$taxes = bdb_get_taxonomies( false );
				ksort( $taxes );
				foreach ( $taxes as $type => $options ) : ?>
					<div class="bookdb-metric">
						<div class="bookdb-metric-inner">
							<p class="top-text"><?php printf( __( '%s Breakdown', 'book-database' ), esc_html( $options['name'] ) ); ?></p>
							<div class="bookdb-loading"></div>
							<div id="<?php echo esc_attr( $type ); ?>-breakdown" class="bookdb-result"></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div id="bookdb-list-of-reviews" class="bookdb-analytics-column">
				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Books Reviewed (20 max)', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<div id="book-list" class="bookdb-result"></div>
					</div>
				</div>
			</div>

		</section>
	</div>
	<?php

}