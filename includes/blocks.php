<?php
/**
 * Gutenberg Blocks
 *
 * @package   wp
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Register Gutenberg blocks
 */
function register_blocks() {

	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	wp_register_script( 'bdb-blocks', BDB_URL . 'assets/js/build/blocks.min.js', array(
		'wp-editor',
	), time() );

	wp_register_style( 'bdb-blocks', BDB_URL . 'assets/css/admin-style-blocks.min.css', array(), time() );

	// Book Grid
	register_block_type( 'book-database/book-grid', array(
		'editor_script'   => 'bdb-blocks',
		'editor_style'    => 'bdb-blocks',
	) );

}

add_action( 'init', __NAMESPACE__ . '\register_blocks' );