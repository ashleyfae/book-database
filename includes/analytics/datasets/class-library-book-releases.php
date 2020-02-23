<?php
/**
 * Dataset: Books Releasing During This Period
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\book_database;
use function Book_Database\format_date;

/**
 * Class Library_Book_Releases
 *
 * @package Book_Database\Analytics
 */
class Library_Book_Releases extends Dataset {

	protected $type = 'template';

	/**
	 * @inheritDoc
	 */
	protected function _get_dataset() {

		$tbl_books    = book_database()->get_table( 'books' )->get_table_name();
		$tbl_author_r = book_database()->get_table( 'book_author_relationships' )->get_table_name();
		$tbl_authors  = book_database()->get_table( 'authors' )->get_table_name();

		$query = "SELECT book.title AS book_title, book.id AS book_id, book.pub_date AS pub_date, book.cover_id AS cover_id,
       GROUP_CONCAT(author.name SEPARATOR ', ') AS author_name
		FROM {$tbl_books} AS book
		LEFT JOIN {$tbl_author_r} AS ar ON( ar.book_id = book.id )
		INNER JOIN {$tbl_authors} AS author ON( ar.author_id = author.id )
		WHERE pub_date IS NOT NULL
		{$this->get_date_condition( 'pub_date', 'pub_date' )}
		GROUP BY book.id
		ORDER BY pub_date DESC
		LIMIT 20";

		$this->log( $query, __CLASS__ );

		$results = $this->get_db()->get_results( $query );

		foreach ( $results as $key => $row ) {
			$results[ $key ]->cover_url            = ! empty( $row->cover_id ) ? wp_get_attachment_image_url( absint( $row->cover_id ), 'medium' ) : '';
			$results[ $key ]->book_title_formatted = sprintf( __( '%s by %s', 'book-database' ), $row->book_title, $row->author_name );
			$results[ $key ]->pub_date_formatted   = format_date( $row->pub_date );
		}

		return $results;

	}

}