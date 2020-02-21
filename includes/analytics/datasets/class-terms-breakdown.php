<?php
/**
 * class-terms-breakdown.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use Book_Database\Rating;
use function Book_Database\book_database;

/**
 * Class Terms_Breakdown
 *
 * @package Book_Database\Analytics
 */
class Terms_Breakdown extends Dataset {

	protected $type = 'table';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$taxonomy    = $this->args['taxonomy'];
		$tbl_log     = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_reviews = book_database()->get_table( 'reviews' )->get_table_name();
		$tbl_term_r  = book_database()->get_table( 'book_term_relationships' )->get_table_name();
		$tbl_terms   = book_database()->get_table( 'book_terms' )->get_table_name();

		$query = $this->get_db()->prepare(
			"SELECT COUNT( log.id ) AS number_books_read, ROUND( AVG( log.rating ), 2 ) AS average_rating, COUNT( review.id ) AS number_reviews, term.name AS term_name
			FROM {$tbl_log} AS log 
			LEFT JOIN {$tbl_reviews} AS review ON ( review.reading_log_id = log.id )
			INNER JOIN {$tbl_term_r} AS tr ON ( tr.book_id = log.book_id )
			INNER JOIN {$tbl_terms} AS term ON ( term.id = tr.term_id )
			WHERE date_finished IS NOT NULL
		  	{$this->get_date_condition( 'date_finished', 'date_finished' )}
			AND term.taxonomy = %s 
			GROUP BY term.taxonomy, term.name 
			ORDER BY term.name ASC",
			$taxonomy
		);

		$this->log( $query, __CLASS__ );

		$results = $this->get_db()->get_results( $query );

		// Format rating.
		foreach ( $results as $key => $result ) {
			$rating                                    = new Rating( $result->average_rating );
			$results[ $key ]->average_rating_formatted = $rating->format_text();
		}

		return $results;

	}
}