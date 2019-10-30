<?php
/**
 * Admin Book Layout Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Format a single book layout item for display in the admin builder.
 *
 * @param string $key
 * @param bool   $disabled Whether or not this item is disabled.
 *
 * @return void
 */
function format_admin_book_layout_option( $key, $disabled = true ) {

	$enabled_fields = get_enabled_book_fields();
	$all_fields     = get_book_fields();

	if ( ! array_key_exists( $key, $all_fields ) ) {
		return;
	}

	$options = $enabled_fields[ $key ] ?? $all_fields[ $key ];

	$classes = array(
		'bdb-book-option'
	);

	if ( 'cover' === $key && array_key_exists( 'alignment', $options ) ) {
		$classes[] = 'bdb-book-cover-align-' . $options['alignment'];
	}

	$classes = implode( ' ', array_map( 'sanitize_html_class', $classes ) );

	$label        = $enabled_fields[ $key ]['label'] ?? $all_fields[ $key ]['label'];
	$label_view   = ( $disabled || empty( $label ) ) ? esc_html( $all_fields[ $key ]['name'] ) : $label;
	$new_line     = $enabled_fields[ $key ]['linebreak'] ?? false;
	$disable_edit = ! empty( $enabled_fields[ $key ]['disable-edit'] ) ? true : false;
	?>
	<div id="bdb-book-option-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>">
		<span class="bdb-book-option-title"><?php echo strip_tags( $label_view, '<a><img><strong><b><em>' ); ?></span>
		<span class="bdb-book-option-name"><?php echo esc_html( $all_fields[ $key ]['name'] ); ?></span>

		<?php if ( ! $disable_edit ) : ?>
			<button type="button" class="bdb-book-option-toggle"><?php esc_html_e( 'Edit', 'book-database' ); ?></button>
		<?php endif; ?>

		<div class="bdb-book-option-fields">
			<label for="bdb-settings-book-layout-<?php echo esc_attr( $key ); ?>-label">
				<?php printf( __( 'Use %s as a placeholder for the %s', 'book-database' ), '<mark>' . $all_fields[ $key ]['placeholder'] . '</mark>', strtolower( $all_fields[ $key ]['name'] ) ); ?>
			</label>

			<textarea id="bdb-settings-book-layout-<?php echo esc_attr( $key ); ?>-label" class="bdb-book-option-label" name="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][label]"><?php echo esc_textarea( $label ); ?></textarea>

			<input type="hidden" class="bdb-book-option-disabled" name="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][disabled]" value="<?php echo $disabled ? 'true' : 'false'; ?>">

			<?php if ( 'cover' == $key ) : ?>
				<?php
				$alignment = $enabled_fields[ $key ]['alignment'] ?? $all_fields[ $key ]['alignment'];
				$size      = $enabled_fields[ $key ]['size'] ?? $all_fields[ $key ]['size'];

				?>
				<label for="bdb-book-layout-cover-changer"><?php _e( 'Cover Alignment', 'book-database' ); ?></label>
				<select id="bdb-book-layout-cover-changer" name="bdb_settings[book_layout][cover][alignment]">
					<?php foreach ( get_book_cover_alignment_options() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $alignment, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>

				<label for="bdb-book-layout-cover-size"><?php _e( 'Cover Size', 'book-database' ); ?></label>
				<select id="bdb-book-layout-cover-size" name="bdb_settings[book_layout][cover][size]">
					<?php foreach ( get_book_cover_image_sizes() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $size, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php else : ?>
				<div class="bdb-new-line-option">
					<input type="checkbox" id="bdb-settings-book-layout-<?php echo esc_attr( $key ); ?>-linebreak" name="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][linebreak]" value="on" <?php checked( $new_line, 'on' ); ?>>
					<label for="bdb-settings-book-layout-<?php echo esc_attr( $key ); ?>-linebreak"><?php _e( 'Add new line after this field', 'book-database' ); ?></label>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php

}