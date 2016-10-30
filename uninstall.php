<?php
/**
 * Uninstall Ultimate Book Blogger
 *
 * @package   book-database
 * @copyright Copyright (c) 2015, Ashley Evans
 * @license   GPL2+
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Load UBB
include_once( 'book-database.php' );

global $wpdb;

// Only delete stuff if that option was checked.
if ( bdb_option( 'uninstall' ) ) {

	/*
	 * Delete Blogroll post type.
	 */
	$bdb_post_types = array(
		'bdb_blogroll'
	);

	foreach ( $bdb_post_types as $post_type ) {

		$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true);
			}
		}
	}

	/*
	 * Delete UBB meta from all posts.
	 */
	$meta_keys = array(
		'_bdb_book_title',
		'_bdb_book_author',
		'_bdb_book_narrator',
		'_bdb_book_series',
		'_bdb_book_publisher',
		'_bdb_book_date',
		'_bdb_book_illustrator',
		'_bdb_book_genre',
		'_bdb_book_pages',
		'_bdb_book_length',
		'_bdb_book_format',
		'_bdb_book_source',
		'_bdb_book_isbn',
		'_bdb_book_asin',
		'_bdb_book_goodreads',
		'_bdb_book_image',
		'_bdb_book_rating',
		'_bdb_book_blurb',
		'_bdb_ftc_disclosure',
		'_bdb_affiliate_disclosure',
		'_bdb_dont_display_review_indexes',
		'_bdb_currently_reading',
		'_bdb_book',
		'_bdb_private_fields',
		'_bdb_book_start',
		'_bdb_book_end',
		'_bdb_book_notes',
		'_bdb_blog_tour_box',
		'_bdb_blog_tour_title',
		'_bdb_blog_tour_banner',
		'_bdb_blog_tour_page',
		'_bdb_giveaway_box',
		'_bdb_giveaways_title',
		'_bdb_giveaway_image',
		'_bdb_giveaway_prize',
		'_bdb_giveaway_end',
		'_bdb_giveaway_winner',
		'_bdb_slider_box',
		'_bdb_slider_title',
		'_bdb_add_to_slider',
		'_bdb_slider_image',
		'_bdb_features_box',
		'_bdb_features_title',
		'_bdb_features_post_title',
		'_bdb_blogroll_box',
		'_bdb_blogroll_title',
		'_bdb_blogroll_name',
		'_bdb_blogroll_url',
		'_bdb_blogroll_rss',
		'_bdb_blogroll_button',
	);

	$allposts = get_posts( array(
		'numberposts' => - 1,
		'post_status' => 'any'
	) );

	foreach ( $allposts as $postinfo ) {
		foreach ($meta_keys as $meta_key) {
			delete_post_meta( $postinfo->ID, $meta_key );
		}
	}

	/*
	 * Delete all taxonomies and terms.
	 */
	$taxonomies = array(
		'book-author',
		apply_filters( 'bdb_audiobook_narrator_tax', 'audiobook-narrator' ),
		apply_filters( 'bdb_illustrator_tax', 'book-illustrator' ),
		'book-series',
		'book-publisher',
		'book-genre',
		'book-source',
		'book-format'
	);

	foreach ( $taxonomies as $taxonomy ) {
		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	// Delete the settings.
	delete_option( 'bdb_settings' );

}