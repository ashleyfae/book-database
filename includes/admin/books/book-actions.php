<?php
/**
 * Book Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Below: Book Information Fields
 */

/**
 * Field: Book Title
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_title_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => sprintf( __( '%s Title', 'book-database' ), bdb_get_label_singular() )
	), array(
		'id'    => 'book_title',
		'name'  => 'book_title',
		'value' => $book->get_title()
	) );
}

add_action( 'book-database/book/information-fields', 'bdb_book_title_field' );

/**
 * Field: Author
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_author_field( $book ) {
	$authors = $book->get_author();

	ob_start();

	$field = ob_get_clean();

	book_database()->html->meta_row( 'raw', array( 'label' => __( 'Author', 'book-database' ), 'field' => $field ) );
}

add_action( 'book-database/book/information-fields', 'bdb_book_author_field' );

/*
 * Below: Saving Functions
 */

function bdb_edit_book( $args ) {

}

add_action( 'book-database/books/edit-book', 'bdb_edit_book' );