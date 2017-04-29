<?php
/**
 * Terms Page
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
 * Terms Page
 *
 * Render the terms page contents.
 *
 * @since 1.0
 * @return void
 */
function bdb_terms_page() {
	$default_views  = bdb_term_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'reviews';

	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[ $requested_view ] ) ) {
		bdb_render_term_view( $requested_view, $default_views );
	} else {
		bdb_terms_list();
	}
}

/**
 * Register the views for term management.
 *
 * @since 1.0
 * @return array
 */
function bdb_term_views() {
	$views = array();

	return apply_filters( 'book-database/terms/views', $views );
}

/**
 * Display List of Terms
 *
 * @since 1.0
 * @return void
 */
function bdb_terms_list() {

	include dirname( __FILE__ ) . '/class-terms-table.php';

	$terms_table = new BDB_Terms_Table();
	$terms_table->prepare_items();

	?>
	<div class="wrap">
		<h1>
			<?php printf( __( '%s Terms', 'book-database' ), bdb_get_label_singular() ); ?>
		</h1>

		<div id="col-container" class="wp-clearfix">

			<div id="col-left">
				<div class="col-wrap">
					<div class="form-wrap">
						<h2><?php _e( 'Add New Term', 'book-database' ); ?></h2>
						<form id="bookdb-add-list" method="POST" action="<?php echo esc_url( bdb_get_admin_page_terms() ); ?>">
							<?php
							/*
							 * Form fields added here.
							 */
							do_action( 'book-database/terms/add-term-fields' );

							wp_nonce_field( 'bdb_add_term', 'bdb_add_term_nonce' );
							?>
							<input type="hidden" name="term_id" value="0">
							<input type="hidden" name="bdb-action" value="terms/add">
							<p class="submit">
								<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add New Term', 'book-database' ); ?>">
							</p>
						</form>
					</div>
				</div>
			</div>

			<div id="col-right">
				<div class="col-wrap">

					<?php do_action( 'book-database/terms/table/top' ); ?>
					<form id="bookdb-terms-filter" method="GET" action="">
						<?php
						$terms_table->search_box( __( 'Search Terms', 'book-database' ), 'bdb-terms' );
						$terms_table->views();
						$terms_table->display();
						?>
						<input type="hidden" name="post_type" value="bdb_terms">
						<input type="hidden" name="page" value="bdb-terms">
						<input type="hidden" name="view" value="terms">
					</form>
					<?php do_action( 'book-database/reviews/table/bottom' ); ?>

				</div>
			</div>

		</div>

	</div>
	<?php
}

/**
 * Render Term View
 *
 * @param string $view      The view being requested.
 * @param array  $callbacks The registered viewas and their callback functions.
 *
 * @since 1.0
 * @return void
 */
function bdb_render_term_view( $view, $callbacks ) {

	$term_id = array_key_exists( 'ID', $_GET ) ? (int) $_GET['ID'] : 0;
	$term    = ! empty( $term_id ) ? bdb_get_term( array( 'term_id' => absint( $term_id ) ) ) : false;
	$render  = true;

	if ( empty( $term ) ) {
		$term              = new stdClass();
		$term->term_id     = 0;
		$term->name        = '';
		$term->description = '';
		$term->type        = '';
		$term->count       = 0;
	}

	switch ( $view ) {
		case 'add' :
			$page_title = __( 'Add New Term', 'book-database' );
			break;

		case 'edit' :
			$page_title = sprintf( __( 'Edit %s', 'book-database' ), bdb_get_taxonomy_name( $term->type ) );
			break;

		default :
			$page_title = __( 'Book Terms', 'book-database' );
			break;
	}

	if ( 'edit' == $view && ! $term->term_id ) {
		bdb_set_error( 'bdb-invalid-term', __( 'Invalid term ID provided.', 'book-database' ) );
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

		<div id="bookdb-term-page-wrapper">
			<form method="POST">
				<?php
				if ( $render ) {
					$callbacks[ $view ]( $term );
				}
				?>
			</form>
		</div>
	</div>
	<?php

}

/**
 * View: Add/Edit Term
 *
 * @param object $term Row from the database.
 *
 * @since 1.0
 * @return void
 */
function bdb_terms_edit_view( $term ) {

	wp_nonce_field( 'bdb_update_term', 'bdb_update_term_nonce' );
	?>
	<input type="hidden" name="term_id" value="<?php echo esc_attr( $term->term_id ); ?>">
	<input type="hidden" name="bdb-action" value="terms/update">

	<table class="form-table">
		<tbody>
		<tr class="form-field form-required term-name-wrap">
			<th scope="row">
				<label for="name"><?php _e( 'Name', 'book-database' ); ?></label>
			</th>
			<td>
				<input type="text" id="name" name="name" size="40" aria-required="true" value="<?php echo esc_attr( $term->name ); ?>">
			</td>
		</tr>
		<tr class="form-field term-slug-wrap">
			<th scope="row">
				<label for="slug"><?php _e( 'Slug', 'book-database' ); ?></label>
			</th>
			<td>
				<input type="text" id="slug" name="slug" size="40" value="<?php echo esc_attr( $term->slug ); ?>">
				<p class="description"><?php _e( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens."', 'book-database' ); ?></p>
			</td>
		</tr>
		<tr class="form-field term-description-wrap">
			<th scope="row">
				<label for="description"><?php _e( 'Description', 'book-database' ); ?></label>
			</th>
			<td>
				<textarea id="description" name="description" rows="5" cols="50" class="large-text"><?php echo esc_textarea( $term->description ); ?></textarea>
			</td>
		</tr>
		</tbody>
	</table>
	<?php
	submit_button( __( 'Update', 'book-database' ) );

}