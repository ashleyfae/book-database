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
			'number' => absint( $number )
		) );

		$old_author_count = count_book_terms( array(
			'taxonomy' => 'author'
		) );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Searching books', 'book-database' ), count( $books ) );

		$author_term_ids_to_delete = array();

		foreach ( $books as $book ) {

			WP_CLI::log( sprintf( __( 'Locating authors for book #%d - %s', 'book-database' ), $book->get_id(), $book->get_title() ) );

			$old_authors = get_attached_book_terms( $book->get_id(), 'author' );

			foreach ( $old_authors as $old_author ) {
				WP_CLI::log( sprintf( __( 'Migrating author: %s', 'book-database' ), $old_author->get_name() ) );

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

}