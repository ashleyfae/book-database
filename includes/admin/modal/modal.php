<?php
/**
 * Modal Contents
 *
 * Lovingly borrowed from WP Recipe Maker.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Modal Content
 *
 * @since 1.0.0
 * @return void
 */
function bdb_add_modal_content() {
	if ( ! bdb_show_media_button() ) {
		return;
	}

	require_once BDB_DIR . 'includes/admin/modal/views/modal.php';
}

add_action( 'admin_footer', 'bdb_add_modal_content' );

/**
 * Get Modal Menu
 *
 * @since 1.0.0
 * @return array
 */
function bdb_get_modal_menu() {
	$menu = array(
		'book' => array(
			'order'       => 100,
			'default'     => true,
			'label'       => esc_html__( 'Book', 'book-database' ),
			'tabs'        => array(
				'book-information' => array(
					'order'    => 100,
					'label'    => esc_html__( 'Book Information', 'book-database' ),
					'template' => BDB_DIR . 'includes/admin/modal/views/tab-book-information.php',
					'callback' => 'insert_update_book'
				)
			),
			'default_tab' => 'book-information'
		)
	);

	// Allow menu to be altered.
	$menu = apply_filters( 'book-database/modal/menu', $menu );

	// Sort menu before returning.
	$sorted_menu = array();
	foreach ( $menu as $menu_item => $options ) {
		uasort( $options['tabs'], 'bdb_modal_sort_by_order' );

		$sorted_menu[ $menu_item ] = $options;
	}

	uasort( $sorted_menu, 'bdb_modal_sort_by_order' );

	return $sorted_menu;
}

/**
 * Sort Array
 *
 * Taken from WP Recipe Maker.
 *
 * @param array $a First array to compare.
 * @param array $b Second array to compare.
 *
 * @since 1.0.0
 * @return mixed
 */
function bdb_modal_sort_by_order( $a, $b ) {
	return $a['order'] - $b['order'];
}

/**
 * Enqueue CSS and JavaScript for Modals
 *
 * @since 1.0.0
 * @return void
 */
function bdb_enqueue_modal_scripts() {
	if ( ! bdb_show_media_button() ) {
		return;
	}

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// CSS

	// JavaScript
	wp_enqueue_script( 'bookdb-modal', BDB_URL . 'assets/js/admin/modal' . $suffix . '.js', array( 'jquery' ), BDB_VERSION, true );
	wp_enqueue_script( 'bookdb-modal-book-form', BDB_URL . 'assets/js/admin/modal-book-form' . $suffix . '.js', array(
		'jquery',
		'bookdb-modal'
	), BDB_VERSION, true );

	wp_localize_script( 'bookdb-modal', 'bdb_modal', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'book-database' ),
		'l10n'     => array(
			'action_button_default' => esc_html__( 'Insert', 'book-database' ),
			'action_button_update'  => __( 'Update', 'book-database' ),
			'media_title'           => __( 'Select or Upload Image', 'book-database' ),
			'media_button'          => __( 'Use Image', 'book-database' ),
			'shortcode_remove'      => __( 'Are you sure you want to remove this book?', 'book-database' ),
		)
	) );
}

add_action( 'admin_enqueue_scripts', 'bdb_enqueue_modal_scripts' );

/**
 * Ajax CB: Get Thumbnail URL from ID
 *
 * @since 1.0.0
 * @return void
 */
function bdb_ajax_get_thumbnail() {
	check_ajax_referer( 'book-database', 'nonce' );

	$image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;
	$thumb    = wp_get_attachment_image_url( $image_id, 'medium' );

	wp_send_json_success( $thumb );

	exit;
}

add_action( 'wp_ajax_bdb_get_thumbnail', 'bdb_ajax_get_thumbnail' );