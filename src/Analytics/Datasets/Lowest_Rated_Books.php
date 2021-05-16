<?php
/**
 * Dataset: Lowest Rated Books
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use Book_Database\Rating;
use function Book_Database\book_database;
use function Book_Database\format_date;

/**
 * Class Lowest_Rated_Books
 *
 * @package Book_Database\Analytics
 */
class Lowest_Rated_Books extends Dataset {

	protected $type = 'template';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_log   = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		// We exclude the highest rated books to avoid table duplicates.
		$query = "SELECT log.date_started AS date_started, log.date_finished AS date_finished, log.rating AS rating, book.id AS book_id, book.title AS book_title
		FROM {$tbl_log} AS log 
		INNER JOIN {$tbl_books} AS book ON ( log.book_id = book.id )
		LEFT JOIN (
			SELECT id
			FROM {$tbl_log}
			WHERE date_finished IS NOT NULL 
			{$this->get_date_condition( 'date_finished', 'date_finished' )}
			ORDER BY rating DESC 
			LIMIT 5
		) AS high_books ON( log.id = high_books.id )
		WHERE rating IS NOT NULL
		AND high_books.id IS NULL
		AND date_finished IS NOT NULL 
		{$this->get_date_condition( 'log.date_finished', 'log.date_finished' )}
		ORDER BY rating ASC 
		LIMIT 5";

		$results = $this->get_db()->get_results( $query );

		foreach ( $results as $key => $row ) {
			$results[ $key ]->date_started_formatted  = format_date( $row->date_started );
			$results[ $key ]->date_finished_formatted = format_date( $row->date_finished );

			$rating = new Rating( $row->rating ?? null );

			$results[ $key ]->rating_formatted = $rating->format_text();
		}

		return $results;

	}

}
