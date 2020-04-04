<?php
/**
 * Dataset: Reading Overview
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Reading_Overview
 *
 * @package Book_Database\Analytics
 */
class Reading_Overview extends Dataset {

	/**
	 * @var string Dataset type
	 */
	protected $type = 'dataset';

	/**
	 * Get an overview of book counts
	 *
	 * @return array
	 */
	protected function _get_dataset() {

		$counts = array(
			'books-finished' => 0,
			'books-dnf'      => 0,
			'rereads'        => 0,
			'new-reads'      => 0
		);

		$tbl_log   = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		/**
		 * Number of books finished
		 */
		$query = "SELECT COUNT(*) FROM {$tbl_log}
				WHERE date_finished IS NOT NULL 
				AND percentage_complete >= 1
				{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		$this->log( $query, __METHOD__ . '\finished' );

		$result                   = $this->get_db()->get_var( $query );
		$counts['books-finished'] = absint( $result );

		/**
		 * Number of DNF
		 */
		$query = "SELECT COUNT(*) FROM {$tbl_log}
				WHERE date_finished IS NOT NULL 
				AND percentage_complete < 1
				{$this->get_date_condition( 'date_finished', 'date_finished' )}";

		//$this->log( $query, __METHOD__ . '\dnf' );

		$result              = $this->get_db()->get_var( $query );
		$counts['books-dnf'] = absint( $result );

		/**
		 * Count rereads
		 */
		$query = $this->get_db()->prepare(
			"SELECT ( COUNT(*) - 1 ) AS count, GROUP_CONCAT( date_finished SEPARATOR ',' ) AS date_finished, book_id
				FROM {$tbl_log} AS log
				INNER JOIN {$tbl_books} AS book ON book.id = log.book_id
				WHERE `date_finished` < %s
				AND `date_finished` IS NOT NULL 
				GROUP BY book_id",
			$this->date_end
		);

		//$this->log( $query, __METHOD__ . '\rereads' );

		$books_read = $this->get_db()->get_results( $query );

		if ( ! empty( $books_read ) ) {
			foreach ( $books_read as $book_read ) {
				if ( $book_read->count < 1 ) {
					continue;
				}

				$dates_finished    = explode( ',', $book_read->date_finished );
				$this_book_rereads = 0;

				foreach ( $dates_finished as $date_finished ) {
					if ( $date_finished >= $this->date_start && $date_finished <= $this->date_end ) {
						$this_book_rereads++;
					}

					if ( $this_book_rereads >= $book_read->count ) {
						break;
					}
				}

				$counts['rereads'] += $this_book_rereads;
			}
		}

		$counts['new-reads'] = ( $counts['books-finished'] + $counts['books-dnf'] ) - $counts['rereads'];

		return array_map( 'absint', $counts );

	}

}