<?php
/**
 * Book Admin Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reviews Page
 *
 * Render the reviews page contents.
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
 * Register the views for review management.
 *
 * @since 1.0.0
 * @return array
 */
function bdb_book_views() {
	$views = array();

	return apply_filters( 'book-database/books/views', $views );
}

/**
 * Display List of Reviews
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
		<form id="ubb-books-filter" method="GET" action="">
			<?php
			$book_table->search_box( sprintf( __( 'Search %s', 'book-database' ), bdb_get_label_plural( true ) ), 'ubb-books' );
			$book_table->display();
			?>
			<input type="hidden" name="post_type" value="bdb_book">
			<input type="hidden" name="page" value="ubb-books">
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
 * @param array  $callbacks The registered viewas and their callback functions.
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

		<div id="ubb-book-page-wrapper">
			<?php
			if ( $render ) {
				$callbacks[ $view ]( $book );
			}
			?>
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
	?>
	<div class="postbox">
		<h2><?php printf( __( '%s Information', 'book-database' ), bdb_get_label_singular() ); ?></h2>
		<div class="inside">
			<?php do_action( 'book-database/book/information-fields', $book ); ?>
		</div>
	</div>
	<?php
}