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

	<div class="bdb-flexbox-container">
		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Editions">
			<h3><?php _e( 'Editions Added', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-table bdb-flexbox-half" data-dataset="Edition_Format_Breakdown" data-canvas="bdb-dataset-edition-format-breakdown">
			<h3><?php _e( 'Format Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-edition-format-breakdown"></div>
			</div>
		</section>
	</div>

	<div class="bdb-analytics-block bdb-dataset-type-graph bdb-flexbox-full" data-dataset="Editions_Over_Time" data-canvas="bdb-dataset-editions-over-time">
		<h3><?php _e( 'Editions Acquired Over Time', 'book-database' ); ?></h3>
		<div>
			<div id="bdb-dataset-editions-over-time" style="min-height: 500px;"></div>
		</div>
	</div>
	<?php
}

add_action( 'book-database/analytics/editions', __NAMESPACE__ . '\editions' );