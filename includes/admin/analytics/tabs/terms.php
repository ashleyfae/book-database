<?php
/**
 * Analytics: Terms
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\get_book_taxonomies;

/**
 * Terms tab
 */
function terms() {
	?>
	<h2><?php _e( 'Terms', 'book-database' ); ?></h2>

	<p><?php _e( 'Results are pulled from books you\'ve read during the selected period.', 'book-database' ); ?></p>

	<div class="bdb-flexbox-container">
		<?php foreach ( get_book_taxonomies() as $taxonomy ) : ?>
			<section class="bdb-analytics-block bdb-dataset-type-template bdb-flexbox-half" data-dataset="Terms_Breakdown" data-arg_taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>">
				<h3><?php printf( __( '%s Breakdown', 'book-database' ), $taxonomy->get_name() ) ?></h3>

				<table class="wp-list-table widefat fixed striped">
					<thead>
					<tr>
						<th class="column-primary"><?php _e( 'Name', 'book-database' ); ?></th>
						<th><?php _e( 'Books Read', 'book-database' ); ?></th>
						<th><?php _e( 'Reviews Written', 'book-database' ); ?></th>
						<th><?php _e( 'Average Rating', 'book-database' ); ?></th>
					</tr>
					</thead>
					<tbody class="bdb-dataset-value">
					<tr>
						<td colspan="4"><?php _e( 'Loading...', 'book-database' ); ?></td>
					</tr>
					</tbody>
				</table>

				<script type="text/html" id="tmpl-bdb-analytics-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>-breakdown" class="bdb-analytics-template">
					<?php require BDB_DIR . 'includes/admin/analytics/templates/tmpl-terms-breakdown.php'; ?>
				</script>

				<script type="text/html" id="tmpl-bdb-analytics-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>-breakdown-none" class="bdb-analytics-template-none">
					<?php require BDB_DIR . 'includes/admin/analytics/templates/tmpl-terms-breakdown-none.php'; ?>
				</script>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
}

add_action( 'book-database/analytics/terms', __NAMESPACE__ . '\terms' );