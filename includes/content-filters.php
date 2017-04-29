<?php
/**
 * Content Filters
 *
 * Replace shortcodes with static HTML as a fallback when the plugin is deactivated.
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
 * Replace the shortcode with the full HTML fallback.
 *
 * @param string $content
 *
 * @since 1.0
 * @return string
 */
function bdb_replace_shortcode_with_fallback( $content ) {

	$book_shortcodes = array();
	$pattern         = get_shortcode_regex( array( 'book' ) );

	if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) && array_key_exists( 2, $matches ) ) {
		foreach ( $matches[2] as $key => $value ) {
			if ( 'book' === $value ) {
				$book_shortcodes[ $matches[0][ $key ] ] = shortcode_parse_atts( stripslashes( $matches[3][ $key ] ) );
			}
		}
	}

	foreach ( $book_shortcodes as $shortcode => $shortcode_options ) {
		if ( ! empty( $shortcode_options ) ) {
			$book_layout = '<!--BDB Book args:' . json_encode( $shortcode_options ) . '-->' . bdb_book_shortcode( $shortcode_options ) . '<!--End BDB Book-->';
			$content     = str_replace( $shortcode, $book_layout, $content );
		}
	}

	return $content;

}

add_filter( 'content_save_pre', 'bdb_replace_shortcode_with_fallback' );

/**
 * Replace the fallback static template with the shortcode
 *
 * @param string $content
 *
 * @since 1.0
 * @return string
 */
function bdb_replace_book_fallback_with_shortcode( $content ) {

	if ( is_feed() ) {
		return $content;
	}

	preg_match_all( '/<!--BDB Book args:(.+?)-->(.+?)<!--End BDB Book-->/ms', $content, $matches );

	foreach ( $matches[0] as $key => $match ) {
		$atts              = json_decode( $matches[1][ $key ] );
		$shortcode_options = '';

		if ( ! empty( $atts ) ) {
			foreach ( $atts as $att_key => $att_value ) {
				$shortcode_options .= ' ' . $att_key . '="' . esc_attr( $att_value ) . '"';
			}
		}
		$content = str_replace( $match, '[book' . $shortcode_options . ']', $content );
	}

	return $content;

}

add_filter( 'the_content', 'bdb_replace_book_fallback_with_shortcode', 1 );
add_filter( 'content_edit_pre', 'bdb_replace_book_fallback_with_shortcode' );