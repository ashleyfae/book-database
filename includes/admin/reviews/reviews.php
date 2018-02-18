<?php
/**
 * Review Page
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
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
 * @since 1.0
 * @return void
 */
function bdb_reviews_page() {
	$default_views = bdb_review_views();
	$requested_view  = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'reviews';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		bdb_render_review_view( $requested_view, $default_views );
	} else {
		bdb_reviews_list();
	}
}

/**
 * Register the views for review management.
 *
 * @since 1.0
 * @return array
 */
function bdb_review_views() {
	$views = array();

	return apply_filters( 'book-database/reviews/views', $views );
}

/**
 * Display List of Reviews
 *
 * @since 1.0
 * @return void
 */
function bdb_reviews_list() {

	include dirname( __FILE__ ) . '/class-review-table.php';

	$review_table = new BDB_Reviews_Table();
	$review_table->prepare_items();

	?>
	<div class="wrap">
		<h1>
			<?php printf( __( '%s Reviews', 'book-database' ), bdb_get_label_singular() ); ?>
			<a href="<?php echo esc_url( bdb_get_admin_page_add_review() ); ?>" class="page-title-action"><?php _e( 'Add New', 'book-database' ); ?></a>
		</h1>
		<?php do_action( 'book-database/reviews/table/top' ); ?>
		<form id="bookdb-reviews-filter" method="GET" action="">
			<?php
			$review_table->search_box( __( 'Search Reviews', 'book-database' ), 'bdb-reviews' );
			$review_table->display();
			?>
			<input type="hidden" name="post_type" value="bdb_review">
			<input type="hidden" name="page" value="bdb-reviews">
			<input type="hidden" name="view" value="reviews">
		</form>
		<?php do_action( 'book-database/reviews/table/bottom' ); ?>
	</div>
	<?php
}

/**
 * Render Review View
 *
 * @param string $view      The view being requested.
 * @param array  $callbacks The registered viewas and their callback functions.
 *
 * @since 1.0
 * @return void
 */
function bdb_render_review_view( $view, $callbacks ) {

	$review_id = array_key_exists( 'ID', $_GET ) ? (int) $_GET['ID'] : 0;
	$review    = new BDB_Review( $review_id );
	$render    = true;

	switch ( $view ) {
		case 'add' :
			$page_title = __( 'Add New Review', 'book-database' );
			break;

		case 'edit' :
			$page_title = __( 'Edit Review', 'book-database' );
			break;

		default :
			$page_title = __( 'Book Reviews', 'book-database' );
			break;
	}

	if ( 'edit' == $view && ! $review->ID ) {
		bdb_set_error( 'bdb-invalid-review', __( 'Invalid review ID provided.', 'book-database' ) );
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

		<div id="bookdb-review-page-wrapper">
			<form method="POST">
				<?php
				if ( $render ) {
					$callbacks[ $view ]( $review );
				}
				?>
			</form>
		</div>
	</div>
	<?php

}

/**
 * View: Add/Edit Review
 *
 * @param BDB_Review $review
 *
 * @since 1.0
 * @return void
 */
function bdb_reviews_edit_view( $review ) {
	wp_nonce_field( 'bdb_save_review', 'bdb_save_review_nonce' );
	?>
	<input type="hidden" name="review_id" value="<?php echo esc_attr( $review->ID ); ?>">
	<input type="hidden" name="bdb-action" value="review/save">

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
										<?php if ( $review->ID ) : ?>
											<a href="<?php echo esc_url( bdb_get_admin_page_delete_review( $review->ID ) ); ?>"><?php _e( 'Delete Review', 'book-database' ); ?></a>
										<?php endif; ?>
									</div>
									<div id="publishing-action">
										<input type="submit" id="bdb-save-review" name="save_review" class="button button-primary button-large" value="<?php esc_attr_e( 'Save', 'book-database' ); ?>">
									</div>
								</div>
							</div>
						</div>

						<?php do_action( 'book-database/review-edit/after-save-box', $review ); ?>
					</div>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_action( 'book-database/review-edit/before-fields', $review ); ?>

					<div class="postbox">
						<h2><?php _e( 'Review Information', 'book-database' ); ?></h2>
						<div class="inside">
							<?php do_action( 'book-database/review-edit/fields', $review ); ?>
						</div>
					</div>

					<?php do_action( 'book-database/review-edit/after-fields', $review ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}