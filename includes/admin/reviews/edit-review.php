<?php
/**
 * Admin Add/Edit Review
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$review = ! empty( $_GET['review_id'] ) ? get_review( absint( $_GET['review_id'] ) ) : false;

if ( ! empty( $_GET['view'] ) && 'edit' === $_GET['view'] && empty( $review ) ) {
	wp_die( __( 'Invalid review ID.', 'book-database' ) );
}
?>
<div class="wrap">
	<h1><?php echo ! empty( $review ) ? __( 'Edit Review', 'book-database' ) : __( 'Add New Review', 'book-database' ); ?></h1>
</div>

<form id="bdb-edit-review" class="bdb-edit-object" method="POST" action="">
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
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

				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables ui-sortables">
						<div id="submitdiv" class="postbox">
							<h2 class="hndle ui-sortable handle"><?php _e( 'Save', 'book-database' ); ?></h2>
							<div class="inside">
								<?php if ( $review instanceof Review && ( $review->get_permalink() ) ) : ?>
									<div id="minor-publishing-actions">
										<a href="<?php echo esc_url( $review->get_permalink() ); ?>" target="_blank" class="button"><?php _e( 'View Review', 'book-database' ); ?></a>
									</div>
								<?php endif; ?>
								<div id="major-publishing-actions">
									<div id="delete-action">
										<?php if ( $review ) : ?>
											<a href="<?php echo esc_url( get_delete_review_url( $review->get_id() ) ); ?>" class="bdb-delete-item" data-object="<?php esc_attr_e( 'review', 'book-database' ); ?>"><?php _e( 'Delete Review', 'book-database' ); ?></a>
										<?php endif; ?>
									</div>
									<div id="publishing-action">
										<input type="submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Save', 'book-database' ); ?>">
									</div>
								</div>
							</div>
						</div>

						<?php do_action( 'book-database/review-edit/after-save-box', $review ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	if ( $review instanceof Review ) {
		wp_nonce_field( 'bdb_update_review', 'bdb_update_review_nonce' );
		?>
		<input type="hidden" id="bdb-review-id" name="review_id" value="<?php echo esc_attr( $review->get_id() ); ?>">
		<?php
	} else {
		wp_nonce_field( 'bdb_add_review', 'bdb_add_review_nonce' );
	}
	?>
</form>