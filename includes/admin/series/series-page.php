<?php
/**
 * Admin Series Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Render the series page
 */
function render_book_series_page() {

	$view = ! empty( $_GET['view'] ) ? urldecode( $_GET['view'] ) : '';

	switch ( $view ) {

		case 'add' :
		case 'edit' :
			require_once BDB_DIR . 'includes/admin/series/edit-series.php';
			break;

		default :
			require_once BDB_DIR . 'includes/admin/series/class-series-list-table.php';

			$list_table = new Series_List_Table();
			$list_table->prepare_items();
			?>
			<div class="wrap">
				<h1>
					<?php esc_html_e( 'Series', 'book-database' ); ?>
					<a href="<?php echo esc_url( get_series_admin_page_url( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'book-database' ); ?></a>
				</h1>

				<form id="bdb-series-filter" method="GET" action="<?php echo esc_url( get_series_admin_page_url() ); ?>">
					<input type="hidden" name="page" value="bdb-series"/>
					<?php
					$list_table->search_box( __( 'Search series', 'book-database' ), 'bdb_search_authors' );
					$list_table->display();
					?>
				</form>
			</div>
			<?php
			break;
	}

}