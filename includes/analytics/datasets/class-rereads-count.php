<?php
/**
 * Number of rereads
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;

/**
 * Class Rereads_Count
 *
 * @package Book_Database\Analytics
 */
class Rereads_Count extends Dataset {

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$rereads   = 0;
		$tbl_log   = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		$query = $this->get_db()->prepare( "SELECT ( COUNT(*) - 1 ) AS count, GROUP_CONCAT( date_finished SEPARATOR ',' ) AS date_finished, book_id
				FROM {$tbl_log} AS log
				INNER JOIN {$tbl_books} AS book ON book.id = log.book_id
				WHERE `date_finished` < %s
				AND `date_finished` IS NOT NULL
				GROUP BY book_id",
			$this->date_end
		);

		$this->log( $query, __METHOD__ );

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

				$rereads += $this_book_rereads;
			}
		}

	}
}