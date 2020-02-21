<?php
/**
 * Analytics: Editions
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Editions tab
 */
function editions() {
	?>
	<h2><?php _e( 'Editions', 'book-database' ); ?></h2>

	<p><?php _e( 'Results are pulled from editions you\'ve acquired over the selected period.', 'book-database' ); ?></p>

	<div class="bdb-flexbox-container">
		<section class="bdb-analytics-block">
			<div class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Editions">
				<h3><?php _e( 'Editions Added', 'book-database' ); ?></h3>
				<span class="bdb-dataset-value"></span>
				<span class="bdb-dataset-period"></span>
			</div>

			<hr>

			<div class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Signed_Editions">
				<h3><?php _e( 'Signed Copies Added', 'book-database' ); ?></h3>
				<span class="bdb-dataset-value"></span>
				<span class="bdb-dataset-period"></span>
			</div>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-chart bdb-flexbox-half" data-dataset="Edition_Genre_Breakdown" data-canvas="bdb-dataset-edition-genre-breakdown">
			<h3><?php _e( 'Genre Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-edition-genre-breakdown"></div>
			</div>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-chart bdb-flexbox-half" data-dataset="Edition_Format_Breakdown" data-canvas="bdb-dataset-edition-format-breakdown">
			<h3><?php _e( 'Format Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-edition-format-breakdown"></div>
			</div>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-chart bdb-flexbox-half" data-dataset="Edition_Source_Breakdown" data-canvas="bdb-dataset-edition-source-breakdown">
			<h3><?php _e( 'Source Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-edition-source-breakdown"></div>
			</div>
		</section>
	</div>

	<div class="bdb-analytics-block bdb-dataset-type-graph bdb-flexbox-full" data-dataset="Editions_Over_Time" data-canvas="bdb-dataset-editions-over-time">
		<h3><?php _e( 'Editions Acquired Over Time', 'book-database' ); ?></h3>
		<div>
			<div id="bdb-dataset-editions-over-time"></div>
		</div>
	</div>
	<?php
}

add_action( 'book-database/analytics/editions', __NAMESPACE__ . '\editions' );