<?php
/**
 * Dataset: Shortest book read
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics\Datasets;

use Book_Database\Analytics\Dataset;
use Book_Database\Models\Book;
use function Book_Database\book_database;
use function Book_Database\get_book;
use function Book_Database\get_books_admin_page_url;

/**
 * Class Shortest_Book_Read
 *
 * @package Book_Database\Analytics
 */
class Shortest_Book_Read extends Dataset {

	protected $orderby = 'ASC';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_log   = book_database()->get_table( 'reading_log' )->get_table_name();
		$tbl_books = book_database()->get_table( 'books' )->get_table_name();

		$query = "SELECT book.id
		FROM {$tbl_books} AS book
		INNER JOIN {$tbl_log} AS log ON( log.book_id = book.id )
		WHERE book.pages IS NOT NULL 
		AND book.pages > 0
		AND date_finished IS NOT NULL 
		AND percentage_complete >= 1
		{$this->get_date_condition( 'log.date_finished', 'log.date_finished' )}
		ORDER BY book.pages {$this->orderby} 
		LIMIT 1";

		$book_id = $this->get_db()->get_var( $query );

		if ( empty( $book_id ) ) {
			return '&ndash;';
		}

		$book = get_book( $book_id );

		if ( ! $book instanceof Book ) {
			return '&ndash;';
		}

		$book_link = '<a href="' . esc_url( get_books_admin_page_url( array( 'view' => 'edit', 'book_id' => $book->get_id() ) ) ) . '">' . sprintf(
				__( '%s by %s', 'book-database' ),
				$book->get_title(),
				$book->get_author_names( true )
			) . '</a><br>';

		$book_link .= '(' . sprintf( _n( '%d page', '%d pages', $book->get_pages(), 'book-database' ), $book->get_pages() ) . ')';

		return $book_link;

	}
}
