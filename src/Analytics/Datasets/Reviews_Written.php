<?php
/**
 * Dataset: Reviews Written
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use Book_Database\ValueObjects\Rating;
use function Book_Database\book_database;
use function Book_Database\format_date;

/**
 * Class Reviews_Written
 *
 * @package Book_Database\Analytics
 */
class Reviews_Written extends Dataset {

	protected $type = 'template';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_reviews  = book_database()->get_table( 'reviews' )->get_table_name();
		$tbl_log      = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();
		$tbl_authors  = book_database()->get_table( 'authors' )->get_table_name();

		// We exclude the highest rated books to avoid table duplicates.
		$query = "SELECT review.id AS review_id, review.date_written AS date_written, log.rating AS rating,
       book.title AS book_title, book.id AS book_id, GROUP_CONCAT(author.name SEPARATOR ', ') AS author_name
		FROM {$tbl_reviews} AS review
		LEFT JOIN {$tbl_log} AS log ON( review.reading_log_id = log.id )
		INNER JOIN {$tbl_books} AS book ON( review.book_id = book.id )
		LEFT JOIN {$tbl_author_r} AS ar ON( ar.book_id = book.id )
		INNER JOIN {$tbl_authors} AS author ON( ar.author_id = author.id )
		WHERE date_written IS NOT NULL 
		{$this->get_date_condition( 'date_written', 'date_written' )}
		GROUP BY review.id
		ORDER BY date_written DESC
		LIMIT 20";

		$this->log( $query, __CLASS__ );

		$results = $this->get_db()->get_results( $query );

		foreach ( $results as $key => $row ) {
			$results[ $key ]->book_title_formatted   = sprintf( __( '%s by %s', 'book-database' ), $row->book_title, $row->author_name );
			$results[ $key ]->date_written_formatted = format_date( $row->date_written );

			$rating = new Rating( $row->rating ?? null );

			$results[ $key ]->rating_class     = $rating->format_html_class();
			$results[ $key ]->rating_formatted = $rating->format_html_stars();
		}

		return $results;

	}

}
