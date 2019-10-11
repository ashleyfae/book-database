<?php
/**
 * Admin Add/Edit Book
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$book = ! empty( $_GET['book_id'] ) ? get_book( absint( $_GET['book_id'] ) ) : false;

if ( ! empty( $_GET['view'] ) && 'edit' === $_GET['view'] && empty( $book ) ) {
	wp_die( __( 'Invalid book ID.', 'book-database' ) );
}
?>
<div class="wrap">
	<h1><?php echo ! empty( $book ) ? __( 'Edit Book', 'book-database' ) : __( 'Add New Book', 'book-database' ); ?></h1>
</div>

<form id="bdb-edit-book" class="bdb-edit-object" method="POST" action="">
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div id="postbox-container-2" class="postbox-container">
					<?php do_action( 'book-database/book-edit/before-information-fields', $book ); ?>

					<div class="postbox">
						<h2><?php _e( 'Book Information', 'book-database' ); ?></h2>
						<div class="inside">
							<?php do_action( 'book-database/book-edit/information-fields', $book ); ?>
						</div>
					</div>

					<?php do_action( 'book-database/book-edit/after-information-fields', $book ); ?>
				</div>

				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortables">
						<div id="submitdiv" class="postbox">
							<h2 class="hndle ui-sortable handle"><?php _e( 'Save', 'book-database' ); ?></h2>
							<div class="inside">
								<div id="major-publishing-actions">
									<div id="delete-action">
										<?php if ( $book ) : ?>
											<a href="<?php echo esc_url( get_delete_book_url( $book->get_id() ) ); ?>"><?php _e( 'Delete Book', 'book-database' ); ?></a>
										<?php endif; ?>
									</div>
									<div id="publishing-action">
										<input type="submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save', 'book-database' ); ?>">
									</div>
								</div>
							</div>
						</div>

						<?php do_action( 'book-database/book-edit/after-save-box', $book ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>