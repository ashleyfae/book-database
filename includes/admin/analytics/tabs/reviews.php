<?php
/**
 * Analytics: Reviews
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Reviews tab
 */
function reviews() {
	?>
	<h2><?php _e( 'Reviews', 'book-database' ); ?></h2>

	<div class="bdb-flexbox-container">
		<section class="bdb-analytics-block bdb-dataset-type-table" data-dataset="Reviews_Written">
			<h3><?php _e( 'Reviews Written (20 max)', 'book-database' ); ?></h3>

			<table class="wp-list-table widefat fixed striped">
				<thead>
				<tr>
					<th class="column-primary"><?php _e( 'Date Written', 'book-database' ); ?></th>
					<th><?php _e( 'Book', 'book-database' ); ?></th>
					<th><?php _e( 'Rating', 'book-database' ); ?></th>
				</tr>
				</thead>
				<tbody class="bdb-dataset-value">
				<tr>
					<td colspan="3"><?php _e( 'Loading...', 'book-database' ); ?></td>
				</tr>
				</tbody>
			</table>

			<script type="text/html" id="tmpl-bdb-analytics-reviews-written" class="bdb-analytics-template">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-reviews-written.php'; ?>
			</script>

			<script type="text/html" id="tmpl-bdb-analytics-reviews-written-none" class="bdb-analytics-template-none">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-reviews-written-none.php'; ?>
			</script>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-table bdb-flexbox-half" data-dataset="Edition_Format_Breakdown" data-canvas="bdb-dataset-edition-format-breakdown">
			<h3><?php _e( 'Format Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-edition-format-breakdown"></div>
			</div>
		</section>
	</div>

	<div class="bdb-analytics-block bdb-dataset-type-graph bdb-flexbox-full" data-dataset="Reviews_Over_Time" data-canvas="bdb-dataset-reviews-over-time">
		<h3><?php _e( 'Reviews Written Over Time', 'book-database' ); ?></h3>
		<div>
			<div id="bdb-dataset-reviews-over-time" style="min-height: 500px;"></div>
		</div>
	</div>
	<?php
}

add_action( 'book-database/analytics/reviews', __NAMESPACE__ . '\reviews' );