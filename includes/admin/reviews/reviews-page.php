<?php
/**
 * Admin Reviews Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Render the reviews page
 */
function render_book_reviews_page() {

	$view = ! empty( $_GET['view'] ) ? urldecode( $_GET['view'] ) : '';

	switch ( $view ) {

		case 'add' :
		case 'edit' :
			require_once BDB_DIR . 'includes/admin/reviews/edit-review.php';
			break;

		default :
			require_once BDB_DIR . 'includes/admin/reviews/class-reviews-list-table.php';

			$list_table = new Reviews_List_Table();
			$list_table->prepare_items();
			?>
			<div class="wrap">
				<h1>
					<?php esc_html_e( 'Reviews', 'book-database' ); ?>
					<?php if ( user_can_edit_books() ) : ?>
						<a href="<?php echo esc_url( get_reviews_admin_page_url( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'book-database' ); ?></a>
					<?php endif; ?>
				</h1>

				<form id="bdb-reviews-filter" method="GET" action="<?php echo esc_url( get_reviews_admin_page_url() ); ?>">
					<input type="hidden" name="page" value="bdb-reviews"/>
					<?php
					$list_table->search_box( __( 'Search reviews', 'book-database' ), 'bdb_search_reviews' );
					$list_table->display();
					?>
				</form>
			</div>
			<?php
			break;
	}

}