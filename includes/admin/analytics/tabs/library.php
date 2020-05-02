<?php
/**
 * Analytics: Library
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Library tab
 */
function library() {
	?>
	<h2><?php _e( 'Library', 'book-database' ); ?></h2>

	<p><?php _e( 'Results are pulled from books you\'ve added to your library over the selected period.', 'book-database' ); ?></p>

	<div class="bdb-flexbox-container">
		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Books_Added">
			<h3><?php _e( 'Books Added', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Series_Books_Added">
			<h3><?php _e( 'Number of Books in a Series', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Standalones_Added">
			<h3><?php _e( 'Number of Standalones', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-value" data-dataset="Number_Distinct_Authors_Added">
			<h3><?php _e( 'Number of Distinct Authors', 'book-database' ); ?></h3>
			<span class="bdb-dataset-value"></span>
			<span class="bdb-dataset-period"></span>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-chart bdb-flexbox-half" data-dataset="Library_Genre_Breakdown" data-canvas="bdb-dataset-edition-genre-breakdown">
			<h3><?php _e( 'Genre Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-edition-genre-breakdown"></div>
			</div>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-template bdb-flexbox-half" data-dataset="Library_Book_Releases">
			<h3><?php _e( 'Books Releasing During This Period (max 20)', 'book-database' ); ?></h3>
			<div class="bdb-flexbox-container bdb-dataset-value bdb-dataset-book-grid"></div>

			<script type="text/html" id="tmpl-bdb-analytics-library-releases" class="bdb-analytics-template">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-book-covers.php'; ?>
			</script>

			<script type="text/html" id="tmpl-bdb-analytics-library-releases-none" class="bdb-analytics-template-none">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-book-covers-none.php'; ?>
			</script>
		</section>
	</div>
	<?php
}

add_action( 'book-database/analytics/library', __NAMESPACE__ . '\library' );