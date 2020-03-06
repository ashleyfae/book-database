<?php
/**
 * Gutenberg Blocks
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Register Gutenberg blocks
 *
 * @since 1.1
 */
function register_blocks() {

	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	wp_register_script( 'bdb-blocks', BDB_URL . 'assets/js/build/blocks.min.js', array(
		'wp-editor',
	), time() );

	$ratings = array(
		array(
			'value' => '',
			'label' => esc_html__( 'All', 'book-database' )
		)
	);
	foreach ( get_available_ratings() as $rating_key => $rating_name ) {
		$ratings[] = array(
			'value' => $rating_key,
			'label' => esc_html( $rating_name )
		);
	}

	wp_localize_script( 'bdb-blocks', 'bdbBlocks', array(
		'ratings' => $ratings
	) );

	wp_register_style( 'bdb-blocks', BDB_URL . 'assets/css/admin-style-blocks.min.css', array(), time() );

	// Book Grid
	register_block_type( 'book-database/book-grid', array(
		'editor_script' => 'bdb-blocks',
		'editor_style'  => 'bdb-blocks',
	) );

}

add_action( 'init', __NAMESPACE__ . '\register_blocks' );

/**
 * Render book-grid block
 *
 * @param string $block_content The block content about to be appended.
 * @param array  $block         The full block, including name and attributes.
 *
 * @since 1.1
 * @return string
 */
function render_book_grid_block( $block_content, $block ) {

	if ( 'book-database/book-grid' !== $block['blockName'] ) {
		return $block_content;
	}

	$attributes = array();

	foreach ( $block['attrs'] as $key => $value ) {
		$attributes[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
	}

	$block_content = trim( '[book-grid ' . implode( ' ', $attributes ) ) . ']';

	return $block_content;
}

add_filter( 'render_block', __NAMESPACE__ . '\render_book_grid_block', 10, 2 );