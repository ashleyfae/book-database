<?php
/**
 * Books Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Render the Book Library page
 */
function render_books_page() {

	$view = ! empty( $_GET['view'] ) ? urldecode( $_GET['view'] ) : '';

	switch ( $view ) {

		case 'add' :
		case 'edit' :
			require_once BDB_DIR . 'includes/admin/books/edit-book.php';
			break;

		default :
			$mode = $_GET['mode'] ?? 'list';

			require_once BDB_DIR . 'includes/admin/books/class-books-list-table.php';

			switch ( $mode ) {
				case 'month' :
					require_once BDB_DIR . 'includes/admin/books/class-monthly-books-list-table.php';
					$list_table = new Monthly_Books_List_Table();
					break;

				default :
					$list_table = new Books_List_Table();
					break;

			}

			$list_table->prepare_items();
			?>
			<div class="wrap">
				<h1>
					<?php esc_html_e( 'Books', 'book-database' ); ?>
					<?php if ( user_can_edit_books() ) : ?>
						<a href="<?php echo esc_url( get_books_admin_page_url( array( 'view' => 'add' ) ) ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'book-database' ); ?></a>
					<?php endif; ?>
				</h1>

				<form id="bdb-books-filter" method="GET" action="<?php echo esc_url( get_books_admin_page_url() ); ?>">
					<input type="hidden" name="page" value="bdb-books"/>
					<div class="wp-filter">
						<?php
						$list_table->view_switcher();
						$list_table->search_box( __( 'Search books', 'book-database' ), 'bdb_search_books' );
						?>
					</div>
					<?php $list_table->display(); ?>
				</form>
			</div>
			<?php
			break;
	}

}