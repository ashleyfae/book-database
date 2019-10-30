<?php
/**
 * Author Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * When a book-author-relationship is added or deleted, recalculate the author `book_count`.
 *
 * @param int $relationship_id ID of the relationship.
 * @param int $author_id     ID of the author.
 * @param int $book_id       ID of the book.
 */
function recalculate_book_author_count_on_relationship_change( $relationship_id, $author_id, $book_id ) {
	recalculate_author_book_count( $author_id );
}

add_action( 'book-database/book-author-relationship/added', __NAMESPACE__ . '\recalculate_book_author_count_on_relationship_change', 10, 3 );
add_action( 'book-database/book-author-relationship/deleted', __NAMESPACE__ . '\recalculate_book_author_count_on_relationship_change', 10, 3 );