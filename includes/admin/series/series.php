<?php
/**
 * Series Page
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
 * Series Page
 *
 * Render the series page content.
 *
 * @since 1.0
 * @return void
 */
function bdb_series_page() {
	$default_series = bdb_series_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'series';

	if ( array_key_exists( $requested_view, $default_series ) && function_exists( $default_series[ $requested_view ] ) ) {
		bdb_render_series_view( $requested_view, $default_series );
	} else {
		bdb_series_list();
	}
}

/**
 * Register the views for series management.
 *
 * @since 1.0
 * @return array
 */
function bdb_series_views() {
	$views = array();

	return apply_filters( 'book-database/series/views', $views );
}

/**
 * Display List of Reviews
 *
 * @since 1.0
 * @return void
 */
function bdb_series_list() {

	include dirname( __FILE__ ) . '/class-series-table.php';

	$series_table = new BDB_Series_Table();
	$series_table->prepare_items();

	?>
	<div class="wrap">
		<h1>
			<?php _e( 'Series', 'book-database' ) ?>
		</h1>
		<?php do_action( 'book-database/series/table/top' ); ?>
		<form id="ubb-series-filter" method="GET" action="">
			<?php
			$series_table->search_box( __( 'Search Series', 'book-database' ), 'ubb-series' );
			$series_table->display();
			?>
			<input type="hidden" name="post_type" value="bdb_series">
			<input type="hidden" name="page" value="bdb-series">
			<input type="hidden" name="view" value="series">
		</form>
		<?php do_action( 'book-database/series/table/bottom' ); ?>
	</div>
	<?php
}

/**
 * Render Series View
 *
 * @param string $view      The view being requested.
 * @param array  $callbacks The registered viewas and their callback functions.
 *
 * @since 1.0
 * @return void
 */
function bdb_render_series_view( $view, $callbacks ) {

	$series_id = array_key_exists( 'ID', $_GET ) ? (int) $_GET['ID'] : 0;
	$series    = new BDB_Series( $series_id );
	$render    = true;

	switch ( $view ) {
		case 'edit' :
			$page_title = __( 'Edit Series', 'book-database' );
			break;

		default :
			$page_title = __( 'Series', 'book-database' );
			break;
	}

	if ( 'edit' == $view && ! $series->ID ) {
		bdb_set_error( 'ubb-invalid-series', __( 'Invalid series ID provided.', 'book-database' ) );
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

		<div id="bookdb-series-page-wrapper">
			<form method="POST">
				<?php
				if ( $render ) {
					$callbacks[ $view ]( $series );
				}
				?>
			</form>
		</div>
	</div>
	<?php

}

/**
 * View: Add/Edit Series
 *
 * @param BDB_Series $series
 *
 * @since 1.0
 * @return void
 */
function bdb_series_edit_view( $series ) {
	wp_nonce_field( 'bdb_save_series', 'bdb_save_series_nonce' );
	?>
	<input type="hidden" name="series_id" value="<?php echo esc_attr( $series->ID ); ?>">
	<input type="hidden" name="bdb-action" value="series/save">

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
										<?php if ( $series->ID ) : ?>
											<a href="<?php echo esc_url( bdb_get_admin_page_delete_series( $series->ID ) ); ?>"><?php _e( 'Delete Series', 'book-database' ); ?></a>
										<?php endif; ?>
									</div>
									<div id="publishing-action">
										<input type="submit" id="bdb-save-series" name="save_series" class="button button-primary button-large" value="<?php esc_attr_e( 'Save', 'book-database' ); ?>">
									</div>
								</div>
							</div>
						</div>

						<?php do_action( 'book-database/series-edit/after-save-box', $series ); ?>
					</div>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_action( 'book-database/series-edit/before-fields', $series ); ?>

					<div class="postbox">
						<h2><?php _e( 'Series Information', 'book-database' ); ?></h2>
						<div class="inside">
							<?php do_action( 'book-database/series-edit/fields', $series ); ?>
						</div>
					</div>

					<?php do_action( 'book-database/series-edit/after-fields', $series ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}