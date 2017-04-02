<?php
/**
 * Review Analytics
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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
			<input type="hidden" id="bookdb-start" value="<?php echo esc_attr( date_i18n( 'Y-1-1', $time ) ); ?>">
			<input type="hidden" id="bookdb-end" value="now">
			<label for="bookdb-range" class="screen-reader-text"><?php _e( 'Select a data range', 'book-database' ); ?></label>
			<select id="bookdb-range">
				<option value="30-days" data-start="-30 days" data-end="now"><?php _e( 'Last 30 days', 'book-database' ); ?></option>
				<option value="this-month" data-start="<?php echo esc_attr( date_i18n( 'Y-m-1', $time ) ); ?>" data-end="now"><?php _e( 'This month', 'book-database' ); ?></option>
				<option value="last-month" data-start="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( '-1 month', strtotime( date_i18n( 'Y-m-1', $time ) ) ) ) ); ?>" data-end="last day of last month"><?php _e( 'Last month', 'book-database' ); ?></option>
				<option value="this-year" data-start="<?php echo esc_attr( date_i18n( 'Y-1-1', $time ) ); ?>" data-end="now" selected><?php _e( 'This year', 'book-database' ); ?></option>
				<option value="last-year" data-start="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( '-1 year', strtotime( date_i18n( 'Y-1-1', $time ) ) ) ) ); ?>" data-end="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( '-1 year', strtotime( date_i18n( 'Y-12-31', $time ) ) ) ) ); ?>"><?php _e( 'Last year', 'book-database' ); ?></option>
				<option value="custom"><?php _e( 'Custom', 'book-database' ); ?></option>
			</select>
			<button type="button" class="button"><?php _e( 'Update', 'book-database' ); ?></button>
		</div>

		<section id="bookdb-review-analytics-metrics">

			<div class="bookdb-analytics-column">
				<div class="bookdb-metric">
					<div class="bookdb-metric-inner bookdb-metric-multi-col">
						<div>
							<p class="top-text"><?php _e( 'Total Books Read', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-books" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-books-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'New Books', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-new" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-new-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'Re-Reads', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-rereads" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-rereads-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'Pages Read', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="pages" class="bookdb-result"></h2>
							<p class="bottom-text" id="pages-compare"><span></span></p>
						</div>
					</div>
					<div id="reading-track" class="bookdb-metric-inner bookdb-result"></div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner bookdb-metric-multi-col">
						<div>
							<p class="top-text"><?php _e( 'Reviews Written', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-reviews" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-reviews-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'Average Rating', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="avg-rating" class="bookdb-result"></h2>
							<p class="bottom-text" id="avg-rating-compare"><span></span></p>
						</div>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner bookdb-metric-multi-col">
						<div>
							<p class="top-text"><?php _e( 'Different Series', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-different-series" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-different-series-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'Standalones', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-standalones" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-standalones-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'Different Authors', 'book-database' ); ?></p>
							<div class="bookdb-loading"></div>
							<h2 id="number-authors" class="bookdb-result"></h2>
							<p class="bottom-text" id="number-authors-compare"><span></span></p>
						</div>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Rating Breakdown', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<div id="rating-breakdown" class="bookdb-result"></div>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Pages Breakdown', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<div id="pages-breakdown" class="bookdb-result"></div>
					</div>
				</div>
			</div>

			<div class="bookdb-analytics-column">
				<?php
				$taxes = bdb_get_taxonomies( false );
				ksort( $taxes );
				foreach ( $taxes as $type => $options ) : ?>
					<div class="bookdb-metric bookdb-term-breakdown">
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
						<p class="top-text"><?php _e( 'Reviews Written (20 max)', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<div id="book-list" class="bookdb-result"></div>
					</div>
				</div>

				<div class="bookdb-metric">
					<div class="bookdb-metric-inner">
						<p class="top-text"><?php _e( 'Read But Not Reviewed (20 max)', 'book-database' ); ?></p>
						<div class="bookdb-loading"></div>
						<div id="read-not-reviewed" class="bookdb-result"></div>
					</div>
				</div>
			</div>

		</section>
	</div>
	<?php

}