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
	 * Exports all books and associated records to a CSV or JSON file
	 *
	 * ## OPTIONS
	 *
	 * <type>
	 * : Export type.
	 *
	 * [--format=<string>]
	 * : Export format
	 *
	 * [--upload-dir=<int>]
	 * : Desired directory to save the file. Default is uploads directory.
	 *
	 * @param array $args
	 * @param array $assoc_args
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function export( $args, $assoc_args ) {
		$type       = $args[0] ?? 'library';
		$format     = WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'csv' );
		$upload_dir = WP_CLI\Utils\get_flag_value( $assoc_args, 'upload-dir', wp_upload_dir()['basedir'] );

		if ( ! is_writeable( $upload_dir ) ) {
			WP_CLI::error( 'Upload directory not writable.' );
		}

		// Create our file.
		$ending    = $format === 'json' ? 'json' : 'csv';
		$file_name = sprintf( 'book-export-%s-%s', $type, date( 'Y-m-d' ) );
		$file_path = trailingslashit( $upload_dir ) . '' . $file_name . '.' . $ending;

		switch ( $type ) {
			case 'library' :
				$this->export_library( $file_path );
				break;
			case 'owned' :
				$this->export_owned( $file_path );
				break;
			case 'read' :
				$this->export_read( $file_path );
				break;
			default :
				WP_CLI::error( 'Invalid export type' );
				break;
		}

		/*$query = new Books_Query();
		$books = $query->get_books( array(
			'number'  => 99999,
			'orderby' => 'book.id',
			'order'   => 'ASC'
		) );

		if ( 'json' === $format ) {
			$this->export_json( $file_path, $books );
		} else {
			$this->export_csv( $file_path, $books );
		}*/

		WP_CLI::success( sprintf( 'Export available at: %s', $file_path ) );

	}

	private function export_library( $file_path ) {
		global $wpdb;

		$query = new Books_Query();
		$books = $query->get_books( array(
			'number'  => 99999,
			'orderby' => 'book.id',
			'order'   => 'ASC'
		) );

		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$headers = array(
			'book_id', 'cover', 'title', 'index_title', 'authors', 'series_id', 'series_name', 'series_position',
			'pub_date', 'pages', 'synopsis', 'goodreads_url', 'average_rating', 'isbn', 'status'
		);

		$headers = array_merge( $headers, get_book_taxonomies( array( 'fields' => 'slug' ) ) );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Exporting books', 'book-database' ), count( $books ) );

		$header_row = '';
		$i          = 1;
		foreach ( $headers as $header ) {
			$header_row .= sprintf( '"%s"', addslashes( $header ) );
			$header_row .= $i === count( $headers ) ? '' : ',';
			$i++;
		}
		$header_row .= "\r\n";

		file_put_contents( $file_path, $header_row );

		foreach ( $books as $book ) {
			$isbn = $wpdb->get_var( $wpdb->prepare(
				"SELECT isbn FROM {$tbl_editions} WHERE book_id = %d AND isbn IS NOT NULL AND isbn != ''",
				$book->id
			) );

			$readingLogs = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$tbl_log} WHERE book_id = %d",
				$book->id
			) );
			$status      = '';
			if ( ! empty( $readingLogs ) ) {
				$status = 'dnf';

				foreach ( $readingLogs as $readingLog ) {
					if ( ! empty( $readingLog->date_finished ) && $readingLog->percentage_complete == 1 ) {
						$status = 'read';
						break;
					} elseif ( ! empty( $readingLog->date_started ) && empty( $readingLog->date_finished ) ) {
						$status = 'currently_reading';
						break;
					}
				}
			}

			$book_row = array(
				'book_id'         => $book->id,
				'cover'           => ! empty( $book->cover_id ) ? wp_get_attachment_image_url( $book->cover_id, 'full' ) : null,
				'title'           => $book->title ?? null,
				'index_title'     => $book->index_title ?? null,
				'authors'         => $book->author_name,
				'series_id'       => $book->series_id ?? null,
				'series_name'     => $book->series_name ?? null,
				'series_position' => $book->series_position ?? null,
				'pub_date'        => $book->pub_date ?? null,
				'pages'           => $book->pages ?? null,
				'synopsis'        => $book->synopsis ?? null,
				'goodreads_url'   => $book->goodreads_url ?? null,
				'average_rating'  => $book->avg_rating ?? null,
				'isbn'            => $isbn ?? null,
				'status'          => $status ?? ''
			);

			// Gather all terms.
			$taxonomies = array();
			$tbl_terms  = book_database()->get_table( 'book_terms' )->get_table_name();
			$tbl_term_r = book_database()->get_table( 'book_term_relationships' )->get_table_name();
			$terms      = $wpdb->get_results( $wpdb->prepare(
				"SELECT term.id, term.taxonomy, term.name, term.slug FROM {$tbl_terms} AS term
					INNER JOIN {$tbl_term_r} AS term_r ON term_r.term_id = term.id
					WHERE term_r.book_id = %d",
				$book->id
			) );
			foreach ( $terms as $term ) {
				$taxonomies[ $term->taxonomy ][] = $term->name;
			}
			foreach ( $taxonomies as $taxonomy => $tax_terms ) {
				$book_row[ $taxonomy ] = implode( ',', $tax_terms );
			}

			$row_data = '';
			$i        = 1;
			foreach ( $headers as $header ) {
				if ( array_key_exists( $header, $book_row ) ) {
					$row_data .= sprintf( '"%s"', addslashes( preg_replace( "/\"/", "'", $book_row[ $header ] ) ) );
				}
				$row_data .= $i === count( $headers ) ? '' : ',';
				$i++;
			}
			$row_data .= "\r\n";

			file_put_contents( $file_path, $row_data, FILE_APPEND );

			$progress->tick();
		}

		$progress->finish();
	}

	private function export_owned( $file_path ) {
		global $wpdb;

		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_owned    = book_database()->get_table( 'editions' )->get_table_name();
		$tbl_terms    = book_database()->get_table( 'book_terms' )->get_table_name();
		$tbl_authors  = book_database()->get_table( 'authors' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();

		$headers = array(
			'book_title', 'author_name', 'isbn', 'format', 'date_acquired', 'signed', 'source', 'date_added'
		);

		$header_row = '';
		$i          = 1;
		foreach ( $headers as $header ) {
			$header_row .= sprintf( '"%s"', addslashes( $header ) );
			$header_row .= $i === count( $headers ) ? '' : ',';
			$i++;
		}
		$header_row .= "\r\n";

		file_put_contents( $file_path, $header_row );

		$query   = "SELECT owned.isbn, owned.format, owned.date_acquired, owned.signed, owned.date_created,
       			author.name AS author_name, book.title AS book_title, source.name AS source
			FROM {$tbl_owned} AS owned
			LEFT JOIN {$tbl_terms} AS source ON(owned.source_id = source.id)
			INNER JOIN {$tbl_books} AS book ON(owned.book_id = book.id)
			INNER JOIN {$tbl_author_r} AS ar ON(book.id = ar.book_id)
			INNER JOIN {$tbl_authors} AS author ON(ar.author_id = author.id)";
		$results = $wpdb->get_results( $query );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Exporting books', 'book-database' ), count( $results ) );

		foreach ( $results as $result ) {
			$row = array(
				'book_title'    => $result->book_title ?? '',
				'author_name'   => $result->author_name ?? '',
				'isbn'          => $result->isbn ?? '',
				'format'        => $result->format ?? '',
				'date_acquired' => $result->date_acquired ?? '',
				'signed'        => $result->signed ?? '',
				'source'        => $result->source ?? '',
				'date_added'    => $result->date_created ?? ''
			);

			$row_data = '';
			$i        = 1;
			foreach ( $headers as $header ) {
				if ( array_key_exists( $header, $row ) ) {
					$row_data .= sprintf( '"%s"', addslashes( preg_replace( "/\"/", "'", $row[ $header ] ) ) );
				}
				$row_data .= $i === count( $headers ) ? '' : ',';
				$i++;
			}
			$row_data .= "\r\n";

			file_put_contents( $file_path, $row_data, FILE_APPEND );

			$progress->tick();
		}

		$progress->finish();
	}

	private function export_read( $file_path ) {
		global $wpdb;

		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();
		$tbl_authors  = book_database()->get_table( 'authors' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();

		$headers = array(
			'book_title', 'author_name', 'isbn', 'date_started', 'date_finished', 'percentage_read', 'rating'
		);

		$header_row = '';
		$i          = 1;
		foreach ( $headers as $header ) {
			$header_row .= sprintf( '"%s"', addslashes( $header ) );
			$header_row .= $i === count( $headers ) ? '' : ',';
			$i++;
		}
		$header_row .= "\r\n";

		file_put_contents( $file_path, $header_row );

		$query   = "SELECT log.date_started, log.date_finished, log.percentage_complete, log.rating, edition.isbn,
       			book.title AS book_title, author.name AS author_name
			FROM {$tbl_log} AS log
			INNER JOIN {$tbl_books} AS book ON(log.book_id = book.id)
			LEFT JOIN {$tbl_editions} AS edition ON(log.edition_id = edition.id)
			INNER JOIN {$tbl_author_r} AS ar ON(book.id = ar.book_id)
			INNER JOIN {$tbl_authors} AS author ON(ar.author_id = author.id)";
		$results = $wpdb->get_results( $query );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Exporting books', 'book-database' ), count( $results ) );

		foreach ( $results as $result ) {
			$row = array(
				'book_title'      => $result->book_title ?? '',
				'author_name'     => $result->author_name ?? '',
				'isbn'            => $result->isbn ?? '',
				'date_started'    => $result->date_started ?? '',
				'date_finished'   => $result->date_finished ?? '',
				'percentage_read' => $result->percentage_complete ?? '',
				'rating'          => $result->rating ?? ''
			);

			$row_data = '';
			$i        = 1;
			foreach ( $headers as $header ) {
				if ( array_key_exists( $header, $row ) ) {
					$row_data .= sprintf( '"%s"', addslashes( preg_replace( "/\"/", "'", $row[ $header ] ) ) );
				}
				$row_data .= $i === count( $headers ) ? '' : ',';
				$i++;
			}
			$row_data .= "\r\n";

			file_put_contents( $file_path, $row_data, FILE_APPEND );

			$progress->tick();
		}

		$progress->finish();
	}

	private function export_csv( $file_path, $books ) {
		global $wpdb;

		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_editions = book_database()->get_table( 'editions' )->get_table_name();

		$headers = array(
			'book_id', 'cover', 'title', 'index_title', 'authors', 'series_id', 'series_name', 'series_position',
			'pub_date', 'pages', 'synopsis', 'goodreads_url', 'average_rating', 'dates_read', 'owned'
		);

		$headers = array_merge( $headers, get_book_taxonomies( array( 'fields' => 'slug' ) ) );

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Exporting books', 'book-database' ), count( $books ) );

		$header_row = '';
		$i          = 1;
		foreach ( $headers as $header ) {
			$header_row .= sprintf( '"%s"', addslashes( $header ) );
			$header_row .= $i === count( $headers ) ? '' : ',';
			$i++;
		}
		$header_row .= "\r\n";

		file_put_contents( $file_path, $header_row );

		foreach ( $books as $book ) {
			$book_row = array(
				'book_id'         => $book->id,
				'cover'           => ! empty( $book->cover_id ) ? wp_get_attachment_image_url( $book->cover_id, 'full' ) : null,
				'title'           => $book->title ?? null,
				'index_title'     => $book->index_title ?? null,
				'authors'         => $book->author_name,
				'series_id'       => $book->series_id ?? null,
				'series_name'     => $book->series_name ?? null,
				'series_position' => $book->series_position ?? null,
				'pub_date'        => $book->pub_date ?? null,
				'pages'           => $book->pages ?? null,
				'synopsis'        => $book->synopsis ?? null,
				'goodreads_url'   => $book->goodreads_url ?? null,
				'average_rating'  => $book->avg_rating ?? null,
			);

			// Gather all terms.
			$taxonomies = array();
			$tbl_terms  = book_database()->get_table( 'book_terms' )->get_table_name();
			$tbl_term_r = book_database()->get_table( 'book_term_relationships' )->get_table_name();
			$terms      = $wpdb->get_results( $wpdb->prepare(
				"SELECT term.id, term.taxonomy, term.name, term.slug FROM {$tbl_terms} AS term
					INNER JOIN {$tbl_term_r} AS term_r ON term_r.term_id = term.id
					WHERE term_r.book_id = %d",
				$book->id
			) );
			foreach ( $terms as $term ) {
				$taxonomies[ $term->taxonomy ][] = $term->name;
			}
			foreach ( $taxonomies as $taxonomy => $tax_terms ) {
				$book_row[ $taxonomy ] = implode( ',', $tax_terms );
			}

			// Get the dates read and link them with editions.
			$reading_logs = $wpdb->get_results( $wpdb->prepare(
				"SELECT log.date_started AS date_started, log.date_finished AS date_finished, log.percentage_complete AS percentage_complete,
				log.rating AS rating, edition.isbn AS isbn, edition.format AS format
				FROM {$tbl_log} AS log
				LEFT JOIN {$tbl_editions} AS edition ON(log.edition_id = edition.id)
				WHERE log.book_id = %d",
				$book->id
			) );

			$dates_read = array();
			if ( $reading_logs ) {
				foreach ( $reading_logs as $log ) {
					$dates_read[] = sprintf(
						'%s|%s|%s|%s|%s|%s',
						$log->isbn,
						$log->date_started,
						$log->date_finished,
						$log->percentage_complete,
						$log->rating,
						$log->format
					);
				}
			}

			$book_row['dates_read'] = implode( ',', $dates_read );

			// Get the owned editions.
			$owned_editions = $wpdb->get_results( $wpdb->prepare(
				"SELECT isbn, format, date_acquired, signed FROM {$tbl_editions}
				WHERE book_id = %d",
				$book->id
			) );

			if ( $owned_editions ) {
				$editions = array();
				foreach ( $owned_editions as $edition ) {
					$editions[] = sprintf(
						'%s|%s|%s|%s',
						$edition->isbn,
						$edition->format,
						$edition->date_acquired,
						$edition->signed
					);
				}
				$book_row['owned'] = implode( ',', $editions );
			}

			$row_data = '';
			$i        = 1;
			foreach ( $headers as $header ) {
				if ( array_key_exists( $header, $book_row ) ) {
					$row_data .= sprintf( '"%s"', addslashes( preg_replace( "/\"/", "'", $book_row[ $header ] ) ) );
				}
				$row_data .= $i === count( $headers ) ? '' : ',';
				$i++;
			}
			$row_data .= "\r\n";

			file_put_contents( $file_path, $row_data, FILE_APPEND );

			$progress->tick();
		}

		$progress->finish();
	}

	private function export_json( $file_path, $books ) {
		global $wpdb;

		$file = array();

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Exporting books', 'book-database' ), count( $books ) );

		foreach ( $books as $book ) {
			// Gather authors.
			$authors      = array();
			$author_ids   = explode( ',', $book->author_id );
			$author_names = explode( ',', $book->author_name );

			if ( ! empty( $author_names ) ) {
				foreach ( $author_names as $author_name_key => $author_name ) {
					$authors[] = array(
						'name' => $author_name,
						'id'   => $author_ids[ $author_name_key ] ?? null
					);
				}
			}

			// Gather editions.
			$editions = array();
			foreach ( get_editions( array( 'book_id' => $book->id, 'number' => 999 ) ) as $edition ) {
				$source = get_book_term( $edition->get_source_id() );

				$editions[] = array(
					'edition_id'    => $edition->get_id(),
					'isbn'          => $edition->get_isbn(),
					'format'        => $edition->get_format(),
					'date_acquired' => $edition->get_date_acquired(),
					'source'        => $source instanceof Book_Term ? $source->get_name() : null,
					'is_signed'     => $edition->is_signed(),
					'date_created'  => $edition->get_date_created(),
					'date_modified' => $edition->get_date_modified()
				);
			}

			// Gather reading logs.
			$reading_logs = array();
			foreach ( get_reading_logs( array( 'book_id' => $book->id, 'number' => 999 ) ) as $reading_log ) {
				$reading_logs[] = array(
					'edition_id'          => $reading_log->get_edition_id(),
					'user_id'             => $reading_log->get_user_id(),
					'date_started'        => $reading_log->get_date_started(),
					'date_finished'       => $reading_log->get_date_finished(),
					'percentage_complete' => $reading_log->get_percentage_complete(),
					'rating'              => $reading_log->get_rating(),
					'date_modified'       => $reading_log->get_date_modified()
				);
			}

			// Gather reviews.
			$reviews = array();
			foreach ( get_reviews( array( 'book_id' => $book->id, 'number' => 999 ) ) as $review ) {
				$reviews[] = array(
					'review_id'      => $review->id,
					'reading_log_id' => $review->reading_log_id,
					'user_id'        => $review->user_id,
					'post_id'        => $review->post_id,
					'external_url'   => $review->url,
					'review'         => $review->review,
					'date_written'   => $review->date_written,
					'date_published' => $review->date_published,
					'date_created'   => $review->date_created,
					'date_modified'  => $review->date_modified
				);
			}

			// Gather all terms.
			$publishers = $genres = $tags = array();
			$tbl_terms  = book_database()->get_table( 'book_terms' )->get_table_name();
			$tbl_term_r = book_database()->get_table( 'book_term_relationships' )->get_table_name();
			$terms      = $wpdb->get_results( $wpdb->prepare(
				"SELECT term.id, term.taxonomy, term.name, term.slug FROM {$tbl_terms} AS term
					INNER JOIN {$tbl_term_r} AS term_r ON term_r.term_id = term.id
					WHERE term_r.book_id = %d",
				$book->id
			) );
			foreach ( $terms as $term ) {
				switch ( $term->taxonomy ) {
					case 'publisher' :
						$publishers[] = $term->name;
						break;
					case 'genre' :
						$genres[] = $term->name;
						break;
					default :
						$tags[ $term->taxonomy ][] = $term->name;
						break;
				}
			}

			$file[] = array(
				'book_id'         => $book->id,
				'cover'           => ! empty( $book->cover_id ) ? wp_get_attachment_image_url( $book->cover_id, 'full' ) : null,
				'title'           => $book->title ?? null,
				'index_title'     => $book->index_title ?? null,
				'authors'         => $authors,
				'series_id'       => $book->series_id ?? null,
				'series_name'     => $book->series_name ?? null,
				'series_position' => $book->series_position ?? null,
				'pub_date'        => $book->pub_date ?? null,
				'pages'           => $book->pages ?? null,
				'synopsis'        => $book->synopsis ?? null,
				'goodreads_url'   => $book->goodreads_url ?? null,
				'average_rating'  => $book->avg_rating ?? null,
				'publishers'      => $publishers,
				'genres'          => $genres,
				'tags'            => $tags,
				'editions'        => $editions,
				'reading_logs'    => $reading_logs,
				'reviews'         => $reviews
			);

			$progress->tick();
		}

		file_put_contents( $file_path, json_encode( $file ) );

		$progress->finish();

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

		$logs  = $wpdb->get_results( "SELECT id,review_id FROM {$tbl_log} WHERE review_id IS NOT NULL AND review_id != 0" );
		$count = ! empty( $logs ) ? count( $logs ) : 0;

		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Updating logs', 'book-database' ), $count );

		if ( ! empty( $logs ) ) {
			foreach ( $logs as $log ) {
				try {
					// Only update if review exists.
					if ( get_review( $log->review_id ) instanceof Review ) {
						update_review( $log->review_id, array(
							'reading_log_id' => absint( $log->id )
						) );
					} else {
						WP_CLI::warning( sprintf( 'Skipping log #%d - review #%d does not exist.', $log->id, $log->review_id ) );
					}

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
