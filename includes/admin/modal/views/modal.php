<?php
/**
 * Modal Template: Modal Content
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$menu = bdb_get_modal_menu();
?>
<div class="bookdb-modal-container">
    <div class="bookdb-modal wp-core-ui">
        <button type="button" class="button-link bookdb-modal-close">
            <span class="bookdb-modal-icon"><span class="screen-reader-text"><?php esc_html_e( 'Close Modal', 'book-database' ); ?></span></span>
        </button>

        <div class="bookdb-modal-content">
            <div class="bookdb-frame wp-core-ui">
                <div class="bookdb-frame-menu">
                    <div class="bookdb-menu">
						<?php
						$active_menu = '';

						foreach ( $menu as $menu_item => $options ) {
							$active       = isset( $options['default'] ) && $options['default'];
							$active_class = $active ? ' active' : '';
							$label        = isset( $options['label'] ) ? $options['label'] : '';
							$default_tab  = isset( $options['default_tab'] ) ? $options['default_tab'] : '';

							if ( $active ) {
								$active_menu = $label;
							}

							echo '<a href="#" class="bookdb-menu-item' . esc_attr( $active_class ) . '" data-menu="' . esc_attr( $menu_item ) . '" data-tab="' . esc_attr( $menu_item ) . '-' . esc_attr( $default_tab ) . '">' . esc_html( $label ) . '</a>';
						}
						?>
                    </div>
                </div>

                <div class="bookdb-frame-title">
                    <h1>
						<?php echo esc_html( $active_menu ); ?><span class="dashicons dashicons-arrow-down"></span>
                    </h1>
                </div>

                <div class="bookdb-frame-router">
					<?php
					foreach ( $menu as $menu_item => $options ) {
						$active       = isset( $options['default'] ) && $options['default'];
						$active_class = $active ? ' active' : '';
						$default_tab  = $active && isset( $options['default_tab'] ) ? $options['default_tab'] : '';

						echo '<div id="bookdb-menu-' . esc_attr( $menu_item ) . '" class="bookdb-router' . esc_attr( $active_class ) . '">';

						foreach ( $options['tabs'] as $tab => $tab_options ) {
							$tab_uid   = $menu_item . '-' . $tab;
							$tab_class = $default_tab == $tab ? ' active' : '';
							$label     = isset( $tab_options['label'] ) ? $tab_options['label'] : '';
							$callback  = isset( $tab_options['callback'] ) ? $tab_options['callback'] : '';
							$init      = isset( $tab_options['init'] ) ? $tab_options['init'] : '';

							echo '<a href="#" class="bookdb-menu-item' . esc_attr( $tab_class ) . '" data-tab="' . esc_attr( $tab_uid ) . '" data-callback="' . esc_attr( $callback ) . '" data-init="' . esc_attr( $init ) . '">' . esc_html( $label ) . '</a>';
						}

						echo '</div>';
					}
					?>
                </div>

                <div class="bookdb-frame-content">
                    <form id="bookdb-modal-form" method="POST">
						<?php
						foreach ( $menu as $menu_item => $options ) {
							$active      = isset( $options['default'] ) && $options['default'];
							$default_tab = $active && isset( $options['default_tab'] ) ? $options['default_tab'] : '';

							foreach ( $options['tabs'] as $tab => $tab_options ) {
								$tab_uid   = $menu_item . '-' . $tab;
								$tab_class = $default_tab == $tab ? ' active' : '';
								$label     = isset( $tab_options['label'] ) ? $tab_options['label'] : '';
								$template  = isset( $tab_options['template'] ) ? $tab_options['template'] : '';

								echo '<div id="bookdb-tab-' . esc_attr( $tab_uid ) . '" class="bookdb-frame-content-tab' . esc_attr( $tab_class ) . '">';

								if ( file_exists( $template ) ) {
									include( $template );
								}

								echo '</div>';
							}
						}
						?>
                    </form>
                </div>

                <div class="bookdb-frame-toolbar">
                    <div class="bookdb-toolbar">
                        <div class="bookdb-toolbar-primary search-form">
                            <button type="button" class="button bookdb-button button-primary button-large bookdb-button-action"><?php esc_html_e( 'Insert', 'book-database' ); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bookdb-modal-backdrop"></div>
</div>
