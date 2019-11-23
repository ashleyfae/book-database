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
			<input type="hidden" id="bdb-end" value="<?php echo esc_attr( date( 'Y-12-31', current_time( 'timestamp' ) ) ); ?>">
			<label for="bdb-range" class="screen-reader-text"><?php _e( 'Select a date range', 'book-database' ); ?></label>
			<select id="bdb-range">
				<option value="30-days" data-start="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>"><?php _e( 'Last 30 days', 'book-database' ); ?></option>
				<option value="this-month" data-start="<?php echo esc_attr( date( 'Y-m-1', current_time( 'timestamp' ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-t', current_time( 'timestamp' ) ) ); ?>"><?php _e( 'This month', 'book-database' ); ?></option>
				<option value="last-month" data-start="<?php echo esc_attr( date( 'Y-m-d', strtotime( '-1 month', strtotime( date( 'Y-m-1', current_time( 'timestamp' ) ) ) ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-m-d', strtotime( 'last day of last month', current_time( 'timestamp' ) ) ) ); ?>"><?php _e( 'Last month', 'book-database' ); ?></option>
				<option value="this-year" data-start="<?php echo esc_attr( date( 'Y-1-1', current_time( 'timestamp' ) ) ); ?>" data-end="<?php echo esc_attr( date( 'Y-12-31', current_time( 'timestamp' ) ) ); ?>" selected><?php _e( 'This year', 'book-database' ); ?></option>
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
							<p class="top-text"><?php _e( 'Books Finished', 'book-database' ); ?></p>
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
							<p class="top-text"><?php _e( 'New Reads', 'book-database' ); ?></p>
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
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>
					</div>
					<div id="bdb-reading-track">
						<div class="bdb-metric-inner bdb-result"></div>
					</div>
				</div> <!--/ Books at a Glance -->

				<!-- Reviews & Ratings -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner bdb-metric-multi-col">
						<div id="bdb-number-reviews">
							<p class="top-text"><?php _e( 'Reviews Written', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-avg-rating">
							<p class="top-text"><?php _e( 'Average Rating', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text" id="bdb-avg-rating-compare"><span></span></p>
						</div>
					</div>
				</div><!--/ Reviews & Ratings -->

				<!-- Series & Authors -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner bdb-metric-multi-col">
						<div id="bdb-number-different-series">
							<p class="top-text"><?php _e( 'Different Series', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-number-standalones">
							<p class="top-text"><?php _e( 'Standalones', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>

						<div id="bdb-number-authors">
							<p class="top-text"><?php _e( 'Different Authors', 'book-database' ); ?></p>
							<div class="bdb-loading"></div>
							<h2 class="bdb-result"></h2>
							<p class="bottom-text bdb-result-compare"><span></span></p>
						</div>
					</div>
				</div><!--/ Series & Authors -->

				<!-- Rating Breakdown -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner">
						<div id="bdb-rating-breakdown">
							<p class="top-text"><?php _e( 'Rating Breakdown', 'book-database' ); ?></p>
							<table>
								<thead>
								<tr>
									<th><?php _e( 'Rating', 'book-database' ); ?></th>
									<th><?php _e( 'Number of Books', 'book-database' ); ?></th>
								</tr>
								</thead>
								<tbody class="bdb-result">
								<tr>
									<td colspan="2">
										<div class="bdb-loading"></div>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div><!--/ Rating Breakdown-->

				<!-- Pages Breakdown -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner">
						<div id="bdb-pages-breakdown">
							<p class="top-text"><?php _e( 'Pages Breakdown', 'book-database' ); ?></p>
							<table>
								<thead>
								<tr>
									<th><?php _e( 'Pages', 'book-database' ); ?></th>
									<th><?php _e( 'Number of Books', 'book-database' ); ?></th>
								</tr>
								</thead>
								<tbody class="bdb-result">
								<tr>
									<td colspan="2">
										<div class="bdb-loading"></div>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div><!--/ Pages Breakdown-->

			</div>

			<!-- Column 2 -->
			<div class="bdb-analytics-column">

				<?php
				$taxonomies = get_book_taxonomies( array(
					'orderby' => 'name',
					'order'   => 'ASC'
				) );

				foreach ( $taxonomies as $taxonomy ) {
					?>
					<!-- Taxonomy Breakdown -->
					<div class="bdb-metric">
						<div class="bdb-metric-inner">
							<div id="bdb-taxonomy-<?php echo esc_attr( $taxonomy->get_slug() ); ?>-breakdown" class="bdb-taxonomy-breakdown" data-taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>">
								<p class="top-text"><?php printf( __( '%s Breakdown', 'book-database' ), esc_html( $taxonomy->get_name() ) ); ?></p>
								<table>
									<thead>
									<tr>
										<th><?php _e( 'Name', 'book-database' ); ?></th>
										<th><?php _e( 'Books Read', 'book-database' ); ?></th>
										<th><?php _e( 'Reviews Written', 'book-database' ); ?></th>
										<th><?php _e( 'Average Rating', 'book-database' ); ?></th>
									</tr>
									</thead>
									<tbody class="bdb-result">
									<tr>
										<td colspan="4">
											<div class="bdb-loading"></div>
										</td>
									</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div><!--/ Taxonomy Breakdown-->
					<?php
				}
				?>

			</div>

			<!-- Column 3 -->
			<div class="bdb-analytics-column">
				<!-- Reviews Written -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner">
						<div id="bdb-reviews-written">
							<p class="top-text"><?php _e( 'Reviews Written (20 max)' ); ?></p>
							<table>
								<thead>
								<tr>
									<th><?php _e( 'Rating', 'book-database' ); ?></th>
									<th><?php _e( 'Book', 'book-database' ); ?></th>
									<th><?php _e( 'Date Written', 'book-database' ); ?></th>
								</tr>
								</thead>
								<tbody class="bdb-result">
								<tr>
									<td colspan="4">
										<div class="bdb-loading"></div>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div><!--/ Reviews Written-->

				<!-- Read But Not Reviewed -->
				<div class="bdb-metric">
					<div class="bdb-metric-inner">
						<div id="bdb-read-not-reviewed">
							<p class="top-text"><?php _e( 'Read But Not Reviewed (20 max)' ); ?></p>
							<table>
								<thead>
								<tr>
									<th><?php _e( 'Rating', 'book-database' ); ?></th>
									<th><?php _e( 'Book', 'book-database' ); ?></th>
									<th><?php _e( 'Date Finished', 'book-database' ); ?></th>
								</tr>
								</thead>
								<tbody class="bdb-result">
								<tr>
									<td colspan="4">
										<div class="bdb-loading"></div>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div><!--/ Read But Not Reviewed -->
			</div>

		</div>
	</div>
	<?php

}