<?php
/**
 * Term Actions
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
 * Display term fields on the "Add New Term" section
 *
 * @since 1.0
 * @return void
 */
function bdb_add_term_fields() {

	$type = isset( $_GET['type'] ) ? wp_strip_all_tags( $_GET['type'] ) : 'author';
	?>
	<div class="form-field form-required term-name-wrap">
		<label for="term-name"><?php _e( 'Name', 'book-database' ); ?></label>
		<input type="text" id="term-name" name="name" size="40" aria-required="true" value="">
	</div>

	<div class="form-field term-slug-wrap">
		<label for="term-slug"><?php _e( 'Slug', 'book-database' ); ?></label>
		<input type="text" id="term-slug" name="slug" size="40" value="">
		<p class="description"><?php _e( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens."', 'book-database' ); ?></p>
	</div>

	<div class="form-field form-required term-type-wrap">
		<label for="term-type"><?php _e( 'Type', 'book-database' ); ?></label>
		<select id="term-type" name="type">
			<?php foreach ( bdb_get_taxonomies( true ) as $id => $taxonomy ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $type ); ?>><?php echo esc_html( $taxonomy['name'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="form-field term-description-wrap">
		<label for="term-description"><?php _e( 'Description', 'book-database' ); ?></label>
		<textarea id="term-description" name="description" rows="5" cols="50" class="large-text"></textarea>
	</div>
	<?php

}

add_action( 'book-database/terms/add-term-fields', 'bdb_add_term_fields' );

/**
 * Insert a new term
 *
 * @since 1.0
 * @return void
 */
function bdb_add_new_term() {

	if ( ! isset( $_POST['bdb_add_term_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['bdb_add_term_nonce'], 'bdb_add_term' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to add terms.', 'book-database' ) );
	}

	$allowed_types = bdb_get_taxonomies();

	$data = array(
		'name'        => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
		'description' => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
		'type'        => ( isset( $_POST['type'] ) && array_key_exists( $_POST['type'], $allowed_types ) ) ? wp_strip_all_tags( $_POST['type'] ) : 'author',
		'count'       => 0
	);

	if ( empty( $data['name'] ) ) {
		wp_die( __( 'Error: Term name is required.', 'book-database' ) );
	}

	if ( empty( $_POST['slug'] ) ) {
		$data['slug'] = bdb_unique_slug( sanitize_title( $data['name'] ), sanitize_text_field( $data['type'] ) );
	} else {
		$data['slug'] = bdb_unique_slug( sanitize_title( $_POST['slug'] ), sanitize_text_field( $data['type'] ) );
	}

	$term_id = book_database()->book_terms->add( $data );

	if ( empty( $term_id ) ) {
		$message = 'term-add-error';
	} else {
		$message = 'term-added';
	}

	$url = add_query_arg( array(
		'bdb-message' => urlencode( $message ),
		'type'        => urlencode( $_POST['type'] )
	), bdb_get_admin_page_terms() );

	wp_safe_redirect( $url );

	exit;

}

add_action( 'book-database/terms/add', 'bdb_add_new_term' );

/**
 * Update an existing term
 *
 * @since 1.0
 * @return void
 */
function bdb_update_term() {

	if ( ! isset( $_POST['bdb_update_term_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['bdb_update_term_nonce'], 'bdb_update_term' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to add terms.', 'book-database' ) );
	}

	$allowed_types = bdb_get_taxonomies();
	$term_id       = absint( $_POST['term_id'] );

	if ( empty( $term_id ) ) {
		wp_die( __( 'Error: Missing term ID.', 'book-database' ) );
	}

	$current_data = bdb_get_term( array( 'term_id' => $term_id ) );

	if ( ! is_object( $current_data ) ) {
		wp_die( __( 'Error fetching current term data.', 'book-database' ) );
	}

	$data = array(
		'name'        => isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '',
		'description' => isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '',
		'type'        => ( isset( $_POST['type'] ) && array_key_exists( $_POST['type'], $allowed_types ) ) ? wp_strip_all_tags( $_POST['type'] ) : 'author',
		'count'       => 0
	);

	if ( empty( $data['name'] ) ) {
		wp_die( __( 'Error: Term name is required.', 'book-database' ) );
	}

	if ( empty( $_POST['slug'] ) ) {
		$data['slug'] = bdb_unique_slug( sanitize_title( $data['name'] ), sanitize_text_field( $data['type'] ) );
	} elseif ( $_POST['slug'] != $current_data->slug ) {
		// Only add the slug if it's different form what already exists.
		$data['slug'] = bdb_unique_slug( sanitize_title( $_POST['slug'] ), sanitize_text_field( $data['type'] ) );
	}

	$result = book_database()->book_terms->update( $term_id, $data );

	if ( empty( $result ) ) {
		$message = 'term-update-error';
	} else {
		$message = 'term-updated';
	}

	$url = add_query_arg( array(
		'bdb-message' => urlencode( $message )
	), bdb_get_admin_page_edit_term( $term_id ) );

	wp_safe_redirect( $url );

	exit;

}

add_action( 'book-database/terms/update', 'bdb_update_term' );

/**
 * Delete a term
 *
 * @since 1.0
 * @return void
 */
function bdb_delete_term() {

	if ( ! isset( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'bdb_delete_term' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! isset( $_GET['ID'] ) ) {
		wp_die( __( 'Missing term ID.', 'book-database' ) );
	}

	$result = book_database()->book_terms->delete( absint( $_GET['ID'] ) );

	$message = $result ? 'term-deleted' : 'term-delete-failed';
	$args    = array(
		'bdb-message' => urlencode( $message )
	);

	if ( isset( $_GET['type'] ) ) {
		$args['type'] = urlencode( $_GET['type'] );
	}

	$url = add_query_arg( $args, bdb_get_admin_page_terms() );

	wp_safe_redirect( $url );

	exit;

}

add_action( 'book-database/terms/delete', 'bdb_delete_term' );