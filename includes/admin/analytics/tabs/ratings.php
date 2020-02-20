<?php
/**
 * Analytics: Ratings
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

/**
 * Rating tab
 */
function ratings() {
	?>
	<h2><?php _e( 'Ratings', 'book-database' ); ?></h2>

	<div class="bdb-flexbox-container">
		<section class="bdb-analytics-block bdb-dataset-type-table bdb-flexbox-third" data-dataset="Ratings_Breakdown" data-canvas="bdb-dataset-ratings-breakdown">
			<h3><?php _e( 'Ratings Breakdown', 'book-database' ); ?></h3>
			<div>
				<div id="bdb-dataset-ratings-breakdown"></div>
			</div>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-table bdb-flexbox-third" data-dataset="Highest_Rated_Books">
			<h3><?php _e( '5 Highest Rated Books', 'book-database' ); ?></h3>

			<table class="wp-list-table widefat fixed striped">
				<thead>
				<tr>
					<th class="column-primary"><?php _e( 'Book', 'book-database' ); ?></th>
					<th><?php _e( 'Rating', 'book-database' ); ?></th>
					<th><?php _e( 'Dates Read', 'book-database' ); ?></th>
				</tr>
				</thead>
				<tbody class="bdb-dataset-value">
				<tr>
					<td colspan="3"><?php _e( 'Loading...', 'book-database' ); ?></td>
				</tr>
				</tbody>
			</table>

			<script type="text/html" id="tmpl-bdb-analytics-highest-rated-books" class="bdb-analytics-template">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-highest-rated-books.php'; ?>
			</script>

			<script type="text/html" id="tmpl-bdb-analytics-highest-rated-books-none" class="bdb-analytics-template-none">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-highest-rated-books-none.php'; ?>
			</script>
		</section>

		<section class="bdb-analytics-block bdb-dataset-type-table bdb-flexbox-third" data-dataset="Lowest_Rated_Books">
			<h3><?php _e( '5 Lowest Rated Books', 'book-database' ); ?></h3>

			<table class="wp-list-table widefat fixed striped">
				<thead>
				<tr>
					<th class="column-primary"><?php _e( 'Book', 'book-database' ); ?></th>
					<th><?php _e( 'Rating', 'book-database' ); ?></th>
					<th><?php _e( 'Dates Read', 'book-database' ); ?></th>
				</tr>
				</thead>
				<tbody class="bdb-dataset-value">
				<tr>
					<td colspan="3"><?php _e( 'Loading...', 'book-database' ); ?></td>
				</tr>
				</tbody>
			</table>

			<script type="text/html" id="tmpl-bdb-analytics-lowest-rated-books" class="bdb-analytics-template">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-lowest-rated-books.php'; ?>
			</script>

			<script type="text/html" id="tmpl-bdb-analytics-lowest-rated-books-none" class="bdb-analytics-template-none">
				<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-lowest-rated-books-none.php'; ?>
			</script>
		</section>
	</div>
	<?php
}

add_action( 'book-database/analytics/ratings', __NAMESPACE__ . '\ratings' );