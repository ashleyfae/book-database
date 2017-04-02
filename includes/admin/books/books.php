<?php
/**
 * Book Admin Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Books Page
 *
 * Render the books page contents.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_books_page() {
	$default_views  = bdb_book_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'books';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		bdb_render_book_view( $requested_view, $default_views );
	} else {
		bdb_books_list();
	}
}

/**
 * Register the views for book management.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_book_views() {
	$views = array();

	return apply_filters( 'book-database/books/views', $views );
}

/**
 * Display List of Books
 *
 * @since 1.0.0
 * @return void
 */
function bdb_books_list() {

	include dirname( __FILE__ ) . '/class-book-table.php';

	$book_table = new BDB_Books_Table();
	$book_table->prepare_items();

	?>
	<div class="wrap">
		<h1>
			<?php echo bdb_get_label_plural(); ?>
			<a href="<?php echo esc_url( bdb_get_admin_page_add_book() ); ?>" class="page-title-action"><?php _e( 'Add New', 'book-database' ); ?></a>
		</h1>
		<?php do_action( 'book-database/books/table/top' ); ?>
		<form id="bookdb-books-filter" method="GET" action="">
			<?php
			$book_table->search_box( sprintf( __( 'Search %s', 'book-database' ), bdb_get_label_plural( true ) ), 'bookdb' );
			$book_table->display();
			?>
			<input type="hidden" name="post_type" value="bdb_book">
			<input type="hidden" name="page" value="bdb-books">
			<input type="hidden" name="view" value="books">
		</form>
		<?php do_action( 'book-database/books/table/bottom' ); ?>
	</div>
	<?php
}

/**
 * Render Book View
 *
 * @param string $view      The view being requested.
 * @param array  $callbacks The registered views and their callback functions.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_render_book_view( $view, $callbacks ) {

	$book_id = array_key_exists( 'ID', $_GET ) ? (int) $_GET['ID'] : 0;
	$book    = new BDB_Book( $book_id );
	$render  = true;

	switch ( $view ) {
		case 'add' :
			$page_title = sprintf( __( 'Add New %s', 'book-database' ), bdb_get_label_singular() );
			break;

		case 'edit' :
			$page_title = sprintf( __( 'Edit %s', 'book-database' ), bdb_get_label_singular() );
			break;

		default :
			$page_title = bdb_get_label_plural();
			break;
	}

	if ( 'edit' == $view && ! $book->ID ) {
		bdb_set_error( 'ubb-invalid-book', __( 'Invalid book ID provided.', 'book-database' ) );
		$render = false;
	}
	?>
	<div class="wrap">
		<h1><?php echo $page_title; ?></h1>
		<?php if ( bdb_get_errors() ) : ?>
			<div class="error settings-error">
				<?php bdb_print_errors(); ?>
			</div>
		<?php endif; ?>

		<div id="bookdb-book-page-wrapper">
			<form method="POST">
				<?php
				if ( $render ) {
					$callbacks[ $view ]( $book );
				}
				?>
			</form>
		</div>
	</div>
	<?php

}

/**
 * View: Add/Edit Book
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_books_edit_view( $book ) {
	wp_nonce_field( 'bdb_save_book', 'bdb_save_book_nonce' );
	?>
	<input type="hidden" name="book_id" value="<?php echo esc_attr( $book->ID ); ?>">
	<input type="hidden" name="bdb-action" value="book/save">

	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortables">
						<div id="submitdiv" class="postbox">
							<h2 class="hndle ui-sortable handle"><?php _e( 'Save', 'book-database' ); ?></h2>
							<div class="inside">
								<div id="major-publishing-actions">
									<div id="delete-action">
										<?php if ( $book->ID ) : ?>
											<a href="<?php echo esc_url( bdb_get_admin_page_delete_book( $book->ID ) ); ?>"><?php printf( __( 'Delete %s', 'book-database' ), bdb_get_label_singular() ); ?></a>
										<?php endif; ?>
									</div>
									<div id="publishing-action">
										<input type="submit" id="bdb-save-book" name="save_book" class="button button-primary button-large" value="<?php esc_attr_e( 'Save', 'book-database' ); ?>">
									</div>
								</div>
							</div>
						</div>

						<?php do_action( 'book-database/book-edit/after-save-box', $book ); ?>
					</div>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_action( 'book-database/book-edit/before-information-fields', $book ); ?>

					<div class="postbox">
						<h2><?php printf( __( '%s Information', 'book-database' ), bdb_get_label_singular() ); ?></h2>
						<div class="inside">
							<?php do_action( 'book-database/book-edit/information-fields', $book ); ?>
						</div>
					</div>

					<?php do_action( 'book-database/book-edit/after-information-fields', $book ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}