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
 * Render the analytics page
 */
function render_analytics_page() {

	?>
	<div id="bdb-book-analytics-wrap" class="wrap">
		<h1><?php _e( 'Reading &amp; Review Analytics', 'book-database' ); ?></h1>

		<div id="bdb-date-range">
			<input type="hidden" id="bdb-start" value="<?php echo esc_attr( date( 'Y-1-1', current_time( 'timestamp' ) ) ); ?>">
			<input type="hidden" id="bdb-end" value="<?php echo esc_attr( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>">
			<label for="bdb-range" class="screen-reader-text"><?php _e( 'Select a date range', 'book-database' ); ?></label>
			<select id="bdb-range">
				<option value="30-days" data-start="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>"><?php _e( 'Last 30 days', 'book-database' ); ?></option>
				<option value="this-month" data-start="<?php echo esc_attr( date( 'Y-m-1', current_time( 'timestamp' ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>"><?php _e( 'This month', 'book-database' ); ?></option>
				<option value="last-month" data-start="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-1 month', strtotime( date( 'Y-m-1', current_time( 'timestamp' ) ) ) ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', strtotime( 'last day of last month', current_time( 'timestamp' ) ) ) ); ?>"><?php _e( 'Last month', 'book-database' ); ?></option>
				<option value="this-year" data-start="<?php echo esc_attr( date( 'Y-1-1', current_time( 'timestamp' ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>" selected><?php _e( 'This year', 'book-database' ); ?></option>
				<option value="last-year" data-start="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-1 year', strtotime( date( 'Y-1-1', current_time( 'timestamp' ) ) ) ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-1 year', strtotime( date( 'Y-12-31', current_time( 'timestamp' ) ) ) ) ) ); ?>"><?php _e( 'Last year', 'book-database' ); ?></option>
				<option value="custom"><?php _e( 'Custom', 'book-database' ); ?></option>
			</select>
			<button type="button" class="button"><?php _e( 'Update', 'book-database' ); ?></button>
		</div>

		<div id="bdb-book-analytics-metrics">

			<!-- Column 1 -->
			<div class="bdb-analytics-column">

				<!-- Books at a Glance -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner bdb-metric-multi-col">
						<div id="bdb-number-books-finished">
							<p class="top-text"><?php _e( 'Total Books Finished', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-number-dnf">
							<p class="top-text"><?php _e( 'DNF', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-number-new-books">
							<p class="top-text"><?php _e( 'New Books', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-number-rereads">
							<p class="top-text"><?php _e( 'Re-Reads', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-number-pages-read">
							<p class="top-text"><?php _e( 'Pages Read', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 id="bdb-pages" class="bdb-result"></h2>
							<p class="bottom-text" id="bdb-pages-compare"><span></span></p>
						</div>
					</div>
					<div id="bdb-reading-track">
						<div class="bdb-metric-inner bdb-result"></div>
					</div>
				</div> <!--/ Books at a Glance -->

				<!-- Reviews & Ratings -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner bdb-metric-multi-col">
						<div>
							<p class="top-text"><?php _e( 'Reviews Written', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 id="bdb-number-reviews" class="bdb-result"></h2>
							<p class="bottom-text" id="number-reviews-compare"><span></span></p>
						</div>

						<div>
							<p class="top-text"><?php _e( 'Average Rating', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 id="avg-rating" class="bdb-result"></h2>
							<p class="bottom-text" id="bdb-avg-rating-compare"><span></span></p>
						</div>
					</div>
				</div><!--/ Reviews & Ratings -->

			</div>

			<!-- Column 2 -->
			<div class="bdb-analytics-column">

			</div>

			<!-- Column 3 -->
			<div class="bdb-analytics-column">

			</div>

		</div>
	</div>
	<?php

}