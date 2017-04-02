<?php
/**
 * Add the Modal Button
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
 * Show Media Button
 *
 * Determines whether or not to display the media button.
 *
 * @since 1.0.0
 * @return bool
 */
function bdb_show_media_button() {
	$current_screen     = get_current_screen();
	$allowed_post_types = bdb_get_review_post_types();
	$display            = false;

	if ( in_array( $current_screen->post_type, $allowed_post_types ) ) {
		$display = true;
	}

	return apply_filters( 'book-database/modal/show-button', $display, $allowed_post_types, $current_screen );
}

/**
 * Add Media Button to Visual Editor
 *
 * @uses  bdb_show_media_button()
 *
 * @param $editor_id
 *
 * @since 1.0.0
 * @return void
 */
function bdb_media_buttons( $editor_id ) {
	if ( ! bdb_show_media_button() ) {
		return;
	}

	?>
	<button type="button" class="button bookdb-modal-button" data-editor="<?php echo esc_attr( $editor_id ); ?>" title="<?php esc_attr_e( 'Insert Book', 'book-database' ); ?>">
		<span class="dashicons dashicons-book"></span>
		<?php esc_html_e( 'Insert Book', 'book-database' ); ?>
	</button>
	<?php
}

add_action( 'media_buttons', 'bdb_media_buttons' );