<?php
/**
 * Register Settings
 *
 * Based on register-settings.php in Easy Digital Downloads.
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
 * Get an Option
 *
 * Looks to see if the specified setting exists, returns the default if not.
 *
 * @param string $key     Key to retrieve
 * @param mixed  $default Default option
 *
 * @global       $bdb_options
 *
 * @since 1.0
 * @return mixed
 */
function bdb_get_option( $key = '', $default = false ) {
	global $bdb_options;

	$value = ! empty( $bdb_options[ $key ] ) ? $bdb_options[ $key ] : $default;
	$value = apply_filters( 'book-database/options/get', $value, $key, $default );

	return apply_filters( 'book-database/options/get/' . $key, $value, $key, $default );
}

/**
 * Update an Option
 *
 * Updates an existing setting value in both the DB and the global variable.
 * Passing in an empty, false, or null string value will remove the key from the bdb_settings array.
 *
 * @param string $key   Key to update
 * @param mixed  $value The value to set the key to
 *
 * @global       $bdb_options
 *
 * @since 1.0
 * @return bool True if updated, false if not
 */
function bdb_update_option( $key = '', $value = false ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = bdb_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'bdb_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'book-database/options/update', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'bdb_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $bdb_options;
		$bdb_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an Option
 *
 * Removes an setting value in both the DB and the global variable.
 *
 * @param string $key The key to delete.
 *
 * @global       $bdb_options
 *
 * @since 1.0
 * @return boolean True if updated, false if not.
 */
function bdb_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'bdb_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'bdb_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $bdb_options;
		$bdb_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array Novelist settings
 */
function bdb_get_settings() {
	$settings = get_option( 'bdb_settings', array() );

	return apply_filters( 'book-database/get-settings', $settings );
}

/**
 * Add all settings sections and fields.
 *
 * @since 1.0
 * @return void
 */
function bdb_register_settings() {

	if ( false == get_option( 'bdb_settings' ) ) {
		add_option( 'bdb_settings' );
	}

	foreach ( bdb_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {
			add_settings_section(
				'bdb_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'bdb_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'bdb_settings[' . $option['id'] . ']',
					$name,
					function_exists( 'bdb_' . $option['type'] . '_callback' ) ? 'bdb_' . $option['type'] . '_callback' : 'bdb_missing_callback',
					'bdb_settings_' . $tab . '_' . $section,
					'bdb_settings_' . $tab . '_' . $section,
					array(
						'section'     => $section,
						'id'          => isset( $option['id'] ) ? $option['id'] : null,
						'desc'        => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'        => isset( $option['name'] ) ? $option['name'] : null,
						'size'        => isset( $option['size'] ) ? $option['size'] : null,
						'options'     => isset( $option['options'] ) ? $option['options'] : '',
						'std'         => isset( $option['std'] ) ? $option['std'] : '',
						'min'         => isset( $option['min'] ) ? $option['min'] : null,
						'max'         => isset( $option['max'] ) ? $option['max'] : null,
						'step'        => isset( $option['step'] ) ? $option['step'] : null,
						'chosen'      => isset( $option['chosen'] ) ? $option['chosen'] : null,
						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null
					)
				);
			}
		}
	}

	// Creates our settings in the options table
	register_setting( 'bdb_settings', 'bdb_settings', 'bdb_settings_sanitize' );

}

add_action( 'admin_init', 'bdb_register_settings' );

/**
 * Registered Settings
 *
 * Sets and returns the array of all plugin settings.
 * Developers can use the following filters to add their own settings or
 * modify existing ones:
 *
 *  + book-database/settings/{key} - Where {key} is a specific tab. Used to modify a single tab/section.
 *  + book-database/settings/registered-settings - Includes the entire array of all settings.
 *
 * @since 1.0
 * @return array
 */
function bdb_get_registered_settings() {

	$bdb_settings = array(
		/* Book Settings */
		'books'   => apply_filters( 'book-database/settings/books', array(
			'main' => array(
				'book_layout' => array(
					'id'   => 'book_layout',
					'name' => sprintf( esc_html__( '%s Layout', 'book-database' ), bdb_get_label_singular() ),
					'type' => 'book_layout',
					'std'  => bdb_get_default_book_layout_keys()
				),
				'taxonomies'  => array(
					'name' => sprintf( esc_html__( '%s Taxonomies', 'book-database' ), bdb_get_label_singular() ),
					'desc' => '', // @todo
					'id'   => 'taxonomies',
					'type' => 'taxonomies',
					'std'  => array(
						// no author option because it's always enabled
						array(
							'id'      => 'publisher',
							'name'    => esc_html__( 'Publisher', 'book-database' ),
							'display' => 'text' // text, checkbox
						),
						array(
							'id'      => 'genre',
							'name'    => esc_html__( 'Genre', 'book-database' ),
							'display' => 'text'
						),
						array(
							'id'      => 'source',
							'name'    => esc_html__( 'Source', 'book-database' ),
							'display' => 'checkbox'
						)
					)
				)
			)
		) ),
		'reviews' => array(
			'main' => array(
				'reviews_page'        => array(
					'id'      => 'reviews_page',
					'name'    => esc_html__( 'Reviews Page', 'book-database' ),
					'desc'    => __( 'The page used for generating all taxonomy archives.', 'book-database' ),
					'type'    => 'select',
					'options' => bdb_get_pages()
				),
				'sync_published_date' => array(
					'id'   => 'sync_published_date',
					'name' => esc_html__( 'Sync Review Publish Date', 'book-database' ),
					'desc' => __( 'When a review is connected to a post, the review publication date will be synced to the post\'s date.', 'book-database' ),
					'type' => 'checkbox',
					'std'  => false
				)
			)
		),
		'misc'    => array(
			'main' => array(
				'delete_on_uninstall' => array(
					'id'   => 'delete_on_uninstall',
					'name' => esc_html__( 'Delete on Uninstall', 'book-database' ),
					'desc' => __( 'Check to permanently delete all plugin data on uninstall.', 'book-database' ),
					'type' => 'checkbox',
					'std'  => false
				)
			)
		)
	);

	return apply_filters( 'book-database/settings/registered-settings', $bdb_settings );

}

/**
 * Sanitize Settings
 *
 * Adds a settings error for the updated message.
 *
 * @param array  $input       The value inputted in the field
 *
 * @global array $bdb_options Array of all the Novelist options
 *
 * @since 1.0
 * @return array New, sanitized settings.
 */
function bdb_settings_sanitize( $input = array() ) {

	global $bdb_options;

	if ( ! is_array( $bdb_options ) ) {
		$bdb_options = array();
	}

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = bdb_get_registered_settings();
	$tab      = ( isset( $referrer['tab'] ) && $referrer['tab'] != 'import_export' ) ? $referrer['tab'] : 'books';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input = $input ? $input : array();
	$input = apply_filters( 'book-database/settings/sanitize/' . $tab . '/' . $section, $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {
		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $section ][ $key ]['type'] ) ? $settings[ $tab ][ $section ][ $key ]['type'] : false;
		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'book-database/settings/sanitize/' . $type, $value, $key );
		}
		// General filter
		$input[ $key ] = apply_filters( 'book-database/settings/sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array();
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();
	$found_settings   = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) && is_array( $bdb_options ) ) {
		foreach ( $found_settings as $key => $value ) {
			if ( empty( $input[ $key ] ) || ! array_key_exists( $key, $input ) ) {
				unset( $bdb_options[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $bdb_options, $input );

	add_settings_error( 'ubb-notices', '', __( 'Settings updated.', 'book-database' ), 'updated' );

	return $output;

}

/**
 * Sanitize Text Field
 *
 * @param string $input
 *
 * @since 1.0
 * @return string
 */
function bdb_settings_sanitize_text_field( $input ) {
	return wp_kses_post( $input );
}

add_filter( 'book-database/settings/sanitize/text', 'bdb_settings_sanitize_text_field' );

/**
 * Sanitize Number Field
 *
 * @param int $input
 *
 * @since 1.0
 * @return int
 */
function bdb_settings_sanitize_number_field( $input ) {
	return intval( $input );
}

add_filter( 'book-database/settings/sanitize/number', 'bdb_settings_sanitize_number_field' );

/**
 * Sanitize Checkbox Field
 *
 * @param int $input
 *
 * @since 1.0
 * @return bool
 */
function bdb_settings_sanitize_checkbox_field( $input ) {
	return ( 1 == intval( $input ) ) ? true : false;
}

add_filter( 'book-database/settings/sanitize/checkbox', 'bdb_settings_sanitize_checkbox_field' );

/**
 * Sanitize Select Field
 *
 * @param string $input
 *
 * @since 1.0
 * @return string
 */
function bdb_settings_sanitize_select_field( $input ) {
	return trim( sanitize_text_field( wp_strip_all_tags( $input ) ) );
}

add_filter( 'book-database/settings/sanitize/select', 'bdb_settings_sanitize_select_field' );

/**
 * Sanitize: Book Layout
 *
 * @param array $input
 *
 * @since 1.0
 * @return array
 */
function bdb_settings_sanitize_book_layout( $input ) {
	$new_input = array();

	foreach ( $input as $key => $value ) {
		if ( array_key_exists( 'disabled', $value ) && $value['disabled'] == 'true' ) {
			continue;
		}

		if ( array_key_exists( 'disabled', $value ) ) {
			unset( $value['disabled'] );
		}

		$new_input[ bdb_sanitize_key( $key ) ] = $value;
	}

	return $new_input;
}

add_filter( 'book-database/settings/sanitize/book_layout', 'bdb_settings_sanitize_book_layout' );

/**
 * Sanitize: Terms
 *
 * @param array $input
 *
 * @since 1.0
 * @return array
 */
function bdb_settings_sanitize_taxonomies( $input ) {
	$new_input = array();

	if ( ! is_array( $input ) ) {
		return $new_input;
	}

	foreach ( $input as $settings ) {
		if ( ! is_array( $settings ) || ! array_key_exists( 'name', $settings ) ) {
			continue;
		}

		$id = ( array_key_exists( 'id', $settings ) && $settings['id'] ) ? $settings['id'] : $settings['name'];

		$new_settings = apply_filters( 'book-database/settings/sanitize/taxonomies/new-settings', array(
			'name'    => trim( sanitize_text_field( $settings['name'] ) ),
			'id'      => trim( sanitize_title( $id ) ),
			'display' => array_key_exists( 'display', $settings ) ? $settings['display'] : 'text'
		), $settings );

		$new_input[] = $new_settings;
	}

	return $new_input;
}

add_filter( 'book-database/settings/sanitize/taxonomies', 'bdb_settings_sanitize_taxonomies' );

/**
 * @todo Add more santizations.
 */

/**
 * Settings Tabs
 *
 * @since 1.0
 * @return array $tabs
 */
function bdb_get_settings_tabs() {
	$tabs            = array();
	$tabs['books']   = esc_html__( 'Books', 'book-database' );
	$tabs['reviews'] = esc_html__( 'Reviews', 'book-database' );
	$tabs['misc']    = esc_html__( 'Misc', 'book-database' );

	return apply_filters( 'book-database/settings/tabs', $tabs );
}


/**
 * Setting Tab Sections
 *
 * @since 1.0
 * @return array $section
 */
function bdb_get_settings_tab_sections( $tab = false ) {
	$tabs     = false;
	$sections = bdb_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  1.0
 * @return array|false Array of tabs and sections
 */
function bdb_get_registered_settings_sections() {
	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'books'   => apply_filters( 'book-database/settings/sections/books', array(
			'main' => esc_html__( 'Book Settings', 'book-database' )
		) ),
		'reviews' => apply_filters( 'book-database/settings/sections/reviews', array(
			'main' => esc_html__( 'Review Settings', 'book-database' )
		) ),
		'misc'    => apply_filters( 'book-database/settings/sections/misc', array(
			'main' => __( 'Misc', 'book-database' ),
		) )
	);

	$sections = apply_filters( 'book-database/settings/sections', $sections );

	return $sections;
}

/**
 * Sanitizes a string key for Book Database Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are
 * allowed
 *
 * @param  string $key String key
 *
 * @since 1.0
 * @return string Sanitized key
 */
function bdb_sanitize_key( $key ) {
	$raw_key = $key;
	$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	return apply_filters( 'book-database/sanitize-key', $key, $raw_key );
}

/**
 * Callbacks
 */

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @param array $args Arguments passed by the setting.
 *
 * @since 1.0
 * @return void
 */
function bdb_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'book-database' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Callback: Text
 *
 * @param array $args Arguments passed by the setting.
 *
 * @since 1.0
 * @return void
 */
function bdb_text_callback( $args ) {
	$saved = bdb_get_option( $args['id'] );

	if ( $saved ) {
		$value = $saved;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="bdb_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$type = ( isset( $args['type'] ) ) ? $args['type'] : 'text';
	?>
    <input type="<?php echo esc_attr( $type ); ?>" class="bookdb-description <?php echo esc_attr( sanitize_html_class( $size ) . '-text' ); ?>" id="bdb_settings_<?php echo bdb_sanitize_key( $args['id'] ); ?>" <?php echo $name; ?> value="<?php echo esc_attr( wp_unslash( $value ) ); ?>">
	<?php if ( $args['desc'] ) : ?>
        <label for="bdb_settings_<?php echo bdb_sanitize_key( $args['id'] ); ?>" class="bookdb-description"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php endif;
}

/**
 * Callback: Checkbox
 *
 * @param array $args Arguments passed by the setting.
 *
 * @since 1.0
 * @return void
 */
function bdb_checkbox_callback( $args ) {
	$saved = bdb_get_option( $args['id'] );

	?>
    <input type="hidden" name="bdb_settings[<?php echo bdb_sanitize_key( $args['id'] ); ?>]" value="-1">
    <input type="checkbox" id="bdb_settings_<?php echo bdb_sanitize_key( $args['id'] ); ?>" name="bdb_settings[<?php echo bdb_sanitize_key( $args['id'] ); ?>]" value="1" <?php checked( 1, $saved ); ?>>
	<?php if ( $args['desc'] ) : ?>
        <label for="bdb_settings_<?php echo bdb_sanitize_key( $args['id'] ); ?>" class="bookdb-description"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php endif;
}

/**
 * Callback: Select
 *
 * @param array $args Arguments passed by the setting.
 *
 * @since 1.0
 * @return void
 */
function bdb_select_callback( $args ) {
	$saved = bdb_get_option( $args['id'] );

	if ( $saved ) {
		$value = $saved;
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( ! is_array( $args['options'] ) ) {
		$args['options'] = array();
	}

	?>
    <select id="bdb_settings_<?php echo bdb_sanitize_key( $args['id'] ); ?>" name="bdb_settings[<?php echo bdb_sanitize_key( $args['id'] ); ?>]">
		<?php foreach ( $args['options'] as $key => $name ) : ?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php endforeach; ?>
    </select>
	<?php if ( $args['desc'] ) : ?>
        <label for="bdb_settings_<?php echo bdb_sanitize_key( $args['id'] ); ?>" class="bookdb-description"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php endif;
}

/**
 * Callback: Book Layout
 *
 * @param array $args
 *
 * @since 1.0
 * @return void
 */
function bdb_book_layout_callback( $args ) {
	$all_fields     = bdb_get_book_fields();
	$enabled_fields = bdb_get_option( $args['id'], false );

	// If we don't have fields already saved, let's use the default values.
	if ( ! is_array( $enabled_fields ) && array_key_exists( 'std', $args ) && is_array( $args['std'] ) ) {

		$enabled_fields = bdb_get_default_book_field_values( $all_fields );

	} elseif ( ! is_array( $enabled_fields ) ) {
		$enabled_fields = array();
	}
	?>
    <div id="book-layout-builder">

        <div id="enabled-book-settings">
            <h3 class="bookdb-no-sort"><?php _e( 'Enabled Fields', 'book-database' ); ?></h3>
            <div id="enabled-book-settings-inner" class="bookdb-sortable bookdb-sorter-enabled-column">
				<?php foreach ( $enabled_fields as $key => $options ) : ?>
					<?php bdb_format_book_layout_option( $key, $options, $all_fields, $enabled_fields, 'false' ); ?>
				<?php endforeach; ?>
            </div>
        </div>

        <div id="available-book-settings">
            <h3 class="bookdb-no-sort"><?php _e( 'Disabled', 'book-database' ); ?></h3>
            <div id="available-book-settings-inner" class="bookdb-sortable">
				<?php foreach ( $all_fields as $key => $options ) : ?>
					<?php
					if ( ! array_key_exists( $key, $enabled_fields ) ) {
						bdb_format_book_layout_option( $key, $options, $all_fields, $enabled_fields, 'true' );
					}
					?>
				<?php endforeach; ?>
            </div>
        </div>

    </div>
	<?php
}

/**
 * Format Book Layout
 *
 * Formats the layout of each book information option used in the book layout.
 *
 * @see   bdb_book_layout_callback()
 *
 * @param string $key
 * @param array  $options
 * @param array  $all_fields
 * @param array  $enabled_fields
 * @param string $disabled
 *
 * @since 1.0
 * @return void
 */
function bdb_format_book_layout_option( $key = '', $options = array(), $all_fields = array(), $enabled_fields = array(), $disabled = 'false' ) {
	if ( ! array_key_exists( $key, $all_fields ) ) {
		return;
	}

	$classes = 'bookdb-book-option';
	if ( $key == 'cover' && array_key_exists( 'alignment', $options ) ) {
		$classes .= ' bookdb-book-cover-align-' . $options['alignment'];
	}

	$label          = ( array_key_exists( $key, $enabled_fields ) && array_key_exists( 'label', $enabled_fields[ $key ] ) ) ? $enabled_fields[ $key ]['label'] : $all_fields[ $key ]['label'];
	$displayed_text = ( $disabled == 'true' || empty( $label ) ) ? esc_html( $all_fields[ $key ]['name'] ) : $label;
	$newline        = ( array_key_exists( $key, $enabled_fields ) && array_key_exists( 'linebreak', $enabled_fields[ $key ] ) ) ? $enabled_fields[ $key ]['linebreak'] : false;
	$disable_edit   = ( array_key_exists( 'disable-edit', $all_fields[ $key ] ) && $all_fields[ $key ]['disable-edit'] ) ? true : false;
	?>
    <div id="bookdb-book-option-<?php echo esc_attr( $key ); ?>" class="<?php echo esc_attr( $classes ); ?>">
        <span class="bookdb-book-option-title"><?php echo strip_tags( $displayed_text, '<a><img><strong><b><em><i>' ); ?></span>
        <span class="bookdb-book-option-name"><?php echo esc_html( $all_fields[ $key ]['name'] ); ?></span>
		<?php if ( $disable_edit === false ) : ?>
            <button type="button" class="bookdb-book-option-toggle"><?php _e( 'Edit', 'book-database' ); ?></button>
		<?php endif; ?>

        <div class="bookdb-book-option-fields">
            <label for="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][label]"><?php printf( __( 'Use <mark>%1$s</mark> as a placeholder for the %2$s', 'book-database' ), $all_fields[ $key ]['placeholder'], strtolower( $all_fields[ $key ]['name'] ) ); ?></label>
            <textarea class="bookdb-book-option-label" id="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][label]" name="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][label]"><?php echo esc_textarea( $label ); ?></textarea>
            <input type="hidden" class="bookdb-book-option-disabled" name="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][disabled]" value="<?php echo esc_attr( $disabled ); ?>">

			<?php if ( $key != 'cover' ) : ?>
                <div class="bookdb-new-line-option">
                    <input type="checkbox" id="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][linebreak]" name="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][linebreak]" value="on" <?php checked( $newline, 'on' ); ?>>
                    <label for="bdb_settings[book_layout][<?php echo esc_attr( $key ); ?>][linebreak]"><?php _e( 'Add new line after this field', 'book-database' ); ?></label>
                </div>
			<?php endif; ?>

			<?php if ( $key == 'cover' ) : ?>
				<?php
				$alignment = ( array_key_exists( $key, $enabled_fields ) && array_key_exists( 'alignment', $enabled_fields[ $key ] ) ) ? $enabled_fields[ $key ]['alignment'] : $all_fields[ $key ]['alignment'];
				$size      = ( array_key_exists( $key, $enabled_fields ) && array_key_exists( 'size', $enabled_fields[ $key ] ) ) ? $enabled_fields[ $key ]['size'] : $all_fields[ $key ]['size'];
				?>
                <label for="bookdb-book-layout-cover-changer"><?php _e( 'Cover Alignment', 'book-database' ); ?></label>
                <select id="bookdb-book-layout-cover-changer" name="bdb_settings[book_layout][cover][alignment]">
					<?php foreach ( bdb_book_alignment_options() as $key => $value ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $alignment, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
                </select>

                <label for="bookdb-book-layout-cover-size"><?php _e( 'Cover Size', 'book-database' ); ?></label>
                <select id="bookdb-book-layout-cover-size" name="bdb_settings[book_layout][cover][size]">
					<?php foreach ( bdb_get_image_sizes() as $key => $value ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $size, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
                </select>
			<?php endif; ?>
        </div>
    </div>
	<?php
}

/**
 * Callback: Terms
 *
 * @param array  $args Arguments passed by the setting.
 *
 * @global array $bdb_options
 *
 * @since 1.0
 * @return void
 */
function bdb_taxonomies_callback( $args ) {
	global $bdb_options;

	if ( isset( $bdb_options[ $args['id'] ] ) ) {
		$value = $bdb_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : array();
	}

	if ( ! is_array( $value ) ) {
		return;
	}

	$i = 0;
	?>
    <table id="bookdb-taxonomies" class="bookdb-table wp-list-table widefat fixed posts">
        <thead>
        <tr>
            <th id="bookdb-term-id"><?php esc_html_e( 'ID', 'book-database' ); ?></th>
            <th id="bookdb-term-name"><?php esc_html_e( 'Name', 'book-database' ); ?></th>
            <th id="bookdb-term-display"><?php esc_html_e( 'Format', 'book-database' ); ?></th>
            <th id="bookdb-term-remove"><?php esc_html_e( 'Remove', 'book-database' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $value as $term ) :
			$name = array_key_exists( 'name', $term ) ? $term['name'] : '';
			$id = array_key_exists( 'id', $term ) ? $term['id'] : sanitize_title( $name );
			$display = array_key_exists( 'display', $term ) ? $term['display'] : 'text';
			?>
            <tr class="bookdb-cloned">
                <td>
                    <label for="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>]_id_<?php echo $i; ?>" class="screen-reader-text"><?php _e( 'ID for the term', 'book-database' ); ?></label>
                    <input type="text" class="regular-text" id="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>]_id_<?php echo $i; ?>" name="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>][<?php echo $i; ?>][id]" value="<?php echo esc_attr( wp_unslash( $id ) ); ?>">
                </td>
                <td>
                    <label for="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>]_name_<?php echo $i; ?>" class="screen-reader-text"><?php _e( 'Name for the term', 'book-database' ); ?></label>
                    <input type="text" class="regular-text" id="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>]_name_<?php echo $i; ?>" name="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>][<?php echo $i; ?>][name]" value="<?php echo esc_attr( wp_unslash( $name ) ); ?>">
                </td>
                <td>
                    <label for="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>]_display_<?php echo $i; ?>" class="screen-reader-text"><?php _e( 'Term display type', 'book-database' ); ?></label>
                    <select id="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>]_display_<?php echo $i; ?>" name="bdb_settings[<?php echo esc_attr( $args['id'] ); ?>][<?php echo $i; ?>][display]">
						<?php foreach ( bdb_get_term_display_types() as $key => $name ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $display, $key ); ?>><?php echo $name; ?></option>
						<?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <button class="button-secondary bookdb-remove-term" onclick="<?php echo ( $i > 0 ) ? 'jQuery(this).parent().parent().remove(); return false' : 'return false'; ?>"><?php esc_html_e( 'Remove', 'book-database' ); ?></button>
                </td>
            </tr>
			<?php
			$i ++;
		endforeach;
		?>
        </tbody>
    </table>

    <div id="bookdb-clone-buttons">
        <button id="bookdb-add-term" class="button button-secondary" rel=".bookdb-cloned"><?php esc_html_e( 'Add Taxonomy', 'book-database' ); ?></button>
    </div>
	<?php
	bdb_get_pages();
}

/**
 * Get Pages
 *
 * Returns a list of all published pages on the site.
 *
 * @param bool $force Force the pages to be loaded even if not on the settings page.
 *
 * @since 1.0
 * @return array
 */
function bdb_get_pages( $force = false ) {

	$pages_options = array( '' => '' );

	if ( ( ! isset( $_GET['page'] ) || 'bdb-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;

}