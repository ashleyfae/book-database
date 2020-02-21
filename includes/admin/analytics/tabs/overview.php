<?php
/**
 * Analytics: Overview
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Overview tab
 */
function overview() {
	?>
	<h2><?php _e( 'Overview', 'book-database' ); ?></h2>

	<div class="bdb-flexbox-container">
		<section class="bdb-analytics-block bdb-dataset-type-dataset bdb-flexbox-half" data-dataset="Reading_Overview">
			<div id="bdb-dataset-books-finished" class="bdb-dataset-section">
				<h3><?php _e( 'Books Finished', 'book-database' ); ?></h3>
				<span class="bdb-dataset-value"></span>
				<span class="bdb-dataset-period"></span>
			</div>
			<div id="bdb-dataset-books-dnf" class="bdb-dataset-section">
				<h3><?php _e( 'DNF', 'book-database' ); ?></h3>
				<span class="bdb-dataset-value"></span>
				<span class="bdb-dataset-period"></span>
			</div>
			<div id="bdb-dataset-new-reads" class="bdb-dataset-section">
				<h3><?php _e( 'New Reads', 'book-database' ); ?></h3>
				<span class="bdb-dataset-value"></span>
				<span class="bdb-dataset-period"></span>
			</div>
			<div id="bdb-dataset-rereads" class="bdb-dataset-section">
				<h3><?php _e( 'Re-Reads', 'book-database' ); ?></h3>
				<span class="bdb-dataset-value"></span>
				<span class="bdb-dataset-period"></span>
			</div>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Different_Series_Read">
			<h3><?php _e( 'Different Series Read', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Standalones_Read">
			<h3><?php _e( 'Standalones Read', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Different_Authors_Read">
			<h3><?php _e( 'Different Authors Read', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Pages_Read">
			<h3><?php _e( 'Pages Read', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Reviews_Written">
			<h3><?php _e( 'Reviews Written', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Average_Rating">
			<h3><?php _e( 'Average Rating', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value bdb-flexbox-full" data-dataset="Reading_Track">
			<h3><?php printf( __( 'On track to read %s books this period', 'book-database' ), '<span class="bdb-dataset-value"></span>' ); ?></h3>
		</section>
	</div>

	<div class="bdb-analytics-block bdb-dataset-type-graph bdb-flexbox-full" data-dataset="Books_Per_Year" data-canvas="bdb-dataset-books-per-year">
		<h3><?php _e( 'Books Read Per Year', 'book-database' ); ?></h3>
		<div>
			<div id="bdb-dataset-books-per-year" style="min-height: 500px;"></div>
		</div>
	</div>
	<?php
}

add_action( 'book-database/analytics/overview', __NAMESPACE__ . '\overview' );