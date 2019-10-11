<?php
/**
 * Register Settings
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Register settings
 *
 * @see sanitize_settings()
 */
function register_settings() {
	register_setting( 'bdb_settings', 'bdb_settings', array(
		'sanitize_callback' => __NAMESPACE__ . '\sanitize_settings',
		'show_in_rest'      => false,
		'default'           => array()
	) );
}

add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );

/**
 * Load settings templates
 */
function load_settings_templates() {

	global $bdb_admin_pages;

	$screen = get_current_screen();

	if ( $screen->id !== $bdb_admin_pages['settings'] ) {
		return;
	}

	$templates = array( 'row', 'row-empty' );

	foreach ( $templates as $template ) {
		?>
		<script type="text/html" id="tmpl-bdb-book-taxonomies-table-<?php echo esc_attr( $template ); ?>">
			<?php require_once BDB_DIR . 'includes/admin/settings/templates/tmpl-taxonomies-table-' . $template . '.php'; ?>
		</script>
		<?php
	}

}
add_action( 'admin_footer', __NAMESPACE__ . '\load_settings_templates' );

/**
 * Sanitize settings
 *
 * @param array $data
 *
 * @return array
 */
function sanitize_settings( $data ) {

	$current_settings = bdb_get_settings();
	$sanitized        = array();

	foreach ( $data as $key => $value ) {
		switch ( $key ) {
			/**
			 * Book layout
			 */
			case 'book_layout' :
				if ( is_array( $value ) ) {
					foreach ( $value as $book_field_key => $book_field ) {
						if ( ! empty( $book_field['disabled'] ) && 'true' === $book_field['disabled'] ) {
							continue;
						}

						$sanitized_book_field = array();

						$sanitized_book_field['label']     = ! empty( $book_field['label'] ) ? sanitize_textarea_field( $book_field['label'] ) : '';
						$sanitized_book_field['linebreak'] = $book_field['linebreak'] ?? false;
						$sanitized_book_field['alignment'] = ( ! empty( $book_field['alignment'] ) && array_key_exists( $book_field['alignment'], get_book_cover_alignment_options() ) ) ? sanitize_text_field( $book_field['alignment'] ) : 'left';
						$sanitized_book_field['size'] = ( ! empty( $book_field['size'] ) && array_key_exists( $book_field['size'], get_book_cover_image_sizes() ) ) ? sanitize_text_field( $book_field['size'] ) : 'full';

						$sanitized[ $key ][ $book_field_key ] = $sanitized_book_field;
					}
				}
				break;
		}
	}

	$sanitized = array_merge( $current_settings, $sanitized );

	return $sanitized;

}