<?php
/**
 * Admin Analytics Actions
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Load analytics templates
 */
function load_analytics_templates() {

	global $bdb_admin_pages;

	$screen = get_current_screen();

	if ( $screen->id !== $bdb_admin_pages['analytics'] ) {
		return;
	}

	$templates = array( 'rating-breakdown', 'pages-breakdown', 'taxonomy-breakdown' );

	foreach ( $templates as $template ) {
		?>
		<script type="text/html" id="tmpl-bdb-analytics-<?php echo esc_attr( $template ); ?>-table-row">
			<?php require_once BDB_DIR . 'includes/admin/analytics/templates/tmpl-' . $template . '-table-row.php'; ?>
		</script>
		<?php
	}

}

add_action( 'admin_footer', __NAMESPACE__ . '\load_analytics_templates' );