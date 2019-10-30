<?php
/**
 * Book Term Relationship Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * When a book-term-relationship is added or deleted, recalculate the book term `count`.
 *
 * @param int $relationship_id ID of the relationship.
 * @param int $term_id         ID of the term.
 * @param int $book_id         ID of the book.
 */
function recalculate_book_term_count_on_relationship_change( $relationship_id, $term_id, $book_id ) {
	recalculate_book_term_count( $term_id );
}

add_action( 'book-database/book-term-relationship/added', __NAMESPACE__ . '\recalculate_book_term_count_on_relationship_change', 10, 3 );
add_action( 'book-database/book-term-relationship/deleted', __NAMESPACE__ . '\recalculate_book_term_count_on_relationship_change', 10, 3 );