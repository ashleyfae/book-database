<?php
/**
 * WP-CLI
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

if ( ! defined( 'WP_CLI' ) || ! class_exists( '\WP_CLI' ) ) {
	return;
}

use WP_CLI;

WP_CLI::add_command( 'bdb', '\Book_Database\CLI' );

class CLI extends \WP_CLI_Command {

	/**
	 * CLI constructor.
	 */
	public function __construct() {

	}

	/**
	 * Migrate authors from the book_terms table to the authors table
	 *
	 * ## OPTIONS
	 *
	 * [--number=<int>]
	 * : Number of book to process. Omit to process them all.
	 *
	 * [--dry-run=<boolean>]
	 * : If true, then no data is actually migrated - the expected migrations actions are just printed for display.
	 * This allows you to see what WILL BE migrated/deleted.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function migrate_authors( $args, $assoc_args ) {

		global $wpdb;

		$number  = $assoc_args['number'] ?? 9999999;
		$dry_run = $assoc_args['dry-run'] ?? false;
		$start   = time();

		$books = get_books( array(
			'number'  => absint( $number ),
			'orderby' => 'id',
			'order'   => 'ASC'
		) );

		$old_author_count = count_book_terms( array(
			'taxonomy' => 'author'
		) );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Searching books', 'book-database' ), count( $books ) );

		$author_term_ids_to_delete = array();

		foreach ( $books as $book ) {

			$old_authors = get_attached_book_terms( $book->get_id(), 'author' );

			foreach ( $old_authors as $old_author ) {

				if ( ! $dry_run ) {
					try {
						// Check for an existing author.
						$existing_author = get_book_author_by( 'slug', $old_author->get_slug() );
						if ( $existing_author instanceof Author ) {
							$new_author_id = $existing_author->get_id();
						} else {
							// Create the new author.
							$new_author_id = add_book_author( array(
								'name'        => $old_author->get_name(),
								'slug'        => $old_author->get_slug(),
								'description' => $old_author->get_description(),
								'image_id'    => $old_author->get_image_id(),
								'links'       => $old_author->get_links(),
								'book_count'  => $old_author->get_book_count()
							) );
						}

						// Create a new relationship.
						set_book_authors( $book->get_id(), $new_author_id, true );

						// Add this old term ID to the delete list.
						$author_term_ids_to_delete[] = $old_author->get_id();
					} catch ( Exception $e ) {
						WP_CLI::error( sprintf( __( 'Failed to create new record for author "%s". Message: %s', 'book-database' ), $old_author->get_name(), $e->getMessage() ) );
					}
				}
			}

			$progress->tick();

		}

		$progress->finish();

		if ( ! $dry_run ) {
			// Delete all old author relationships.
			$author_term_ids_to_delete = array_unique( $author_term_ids_to_delete );

			$progress = \WP_CLI\Utils\make_progress_bar( __( 'Deleting old author terms', 'book-database' ), count( $author_term_ids_to_delete ) );

			foreach ( $author_term_ids_to_delete as $author_term_id ) {
				try {
					delete_book_term( $author_term_id );
					$progress->tick();
				} catch ( Exception $e ) {
					WP_CLI::error( sprintf( __( 'Failed to delete old author term ID #%d. Message: %s', 'book-database' ), $author_term_id, $e->getMessage() ) );
				}
			}

			$progress->finish();

			// Delete the author taxonomy.
			$taxonomy = get_book_taxonomy_by( 'slug', 'author' );

			if ( $taxonomy ) {
				try {
					delete_book_taxonomy( $taxonomy->get_id() );
				} catch ( Exception $e ) {
					WP_CLI::error( sprintf( __( 'Failed to delete the "author" taxonomy. Message: %s', 'book-database' ), $e->getMessage() ) );
				}
			}
		}

		$new_author_count = count_book_authors();

		WP_CLI::log( sprintf( __( 'Authors migrated in %d seconds. Old Count: %d; New Count: %d', 'book-database' ), time() - $start, $old_author_count, $new_author_count ) );

	}

	/**
	 * Add `reading_log_id` column to reviews table, and remove old `review_id` association from
	 * reading log table.
	 *
	 * ## OPTIONS
	 *
	 * [--delete=<boolean>]
	 * : If true, then the old data is wiped. If false, it's not.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function migrate_reading_logs( $args, $assoc_args ) {

		global $wpdb;

		$delete = $assoc_args['delete'] ?? false;
		$start  = time();

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$logs  = $wpdb->get_results( "SELECT id,review_id FROM {$tbl_log} WHERE review_id IS NOT NULL" );
		$count = ! empty( $logs ) ? count( $logs ) : 0;

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Updating logs', 'book-database' ), $count );

		if ( ! empty( $logs ) ) {
			foreach ( $logs as $log ) {
				try {
					update_review( $log->review_id, array(
						'reading_log_id' => absint( $log->id )
					) );

					if ( $delete ) {
						update_reading_log( $log->id, array(
							'review_id' => null
						) );
					}

					$progress->tick();
				} catch ( Exception $e ) {
					WP_CLI::error( sprintf( __( 'Error on log #%d. Message: %s', 'book-database' ), $log->id, $e->getMessage() ) );
				}
			}
		}

		if ( $delete ) {
			$wpdb->query( "ALTER TABLE {$tbl_log} DROP COLUMN review_id" );
		}

		$progress->finish();

		WP_CLI::log( sprintf( __( 'Reading log IDs migrated in %d seconds.', 'book-database' ), time() - $start ) );

	}

	/**
	 * Migrate purchase links from `buy_link` column in `wp_bdb_books` to custom tables.
	 *
	 * ## OPTIONS
	 *
	 * [--delete=<boolean>]
	 * : If true, then the old data is wiped. If false, it's not.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function migrate_purchase_links( $args, $assoc_args ) {

		global $wpdb;

		$delete = $assoc_args['delete'] ?? false;
		$start  = time();

		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		$books = $wpdb->get_results( "SELECT id,buy_link FROM {$tbl_books} WHERE buy_link != ''" );
		$count = ! empty( $books ) ? count( $books ) : 0;

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Updating purchase links', 'book-database' ), $count );

		if ( ! empty( $books ) ) {
			foreach ( $books as $book ) {
				try {
					$url_parts     = parse_url( $book->buy_link );
					$retailer_name = $url_parts['host'];

					// Maybe add retailer.
					$retailer = get_retailer_by( 'name', $retailer_name );
					if ( $retailer instanceof Retailer ) {
						$retailer_id = $retailer->get_id();
					} else {
						$retailer_id = add_retailer( array(
							'name' => sanitize_text_field( $retailer_name )
						) );
					}

					// Insert book link.
					add_book_link( array(
						'book_id'     => absint( $book->id ),
						'retailer_id' => absint( $retailer_id ),
						'url'         => $book->buy_link
					) );

					$progress->tick();
				} catch ( Exception $e ) {
					WP_CLI::error( sprintf( __( 'Error on book #%d. Message: %s', 'book-database' ), $book->id, $e->getMessage() ) );
				}
			}
		}

		if ( $delete ) {
			$wpdb->query( "ALTER TABLE {$tbl_books} DROP COLUMN buy_link" );
		}

		$progress->finish();

		WP_CLI::log( sprintf( __( 'Purchase links migrated in %d seconds. Old Count: %d; New Count: %d', 'book-database' ), time() - $start, count( $books ), count_book_links() ) );

	}

}