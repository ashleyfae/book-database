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
		'name'  => 'title',
		'value' => $book->get_title()
	) );
}

add_action( 'book-database/book-edit/information-fields', 'bdb_book_title_field' );

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

add_action( 'book-database/book-edit/information-fields', 'bdb_book_author_field' );

/**
 * Field: Book Series
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_series_field( $book ) {

	$series_args          = array(
		'id'    => 'book_series_name',
		'name'  => 'series_name',
		'value' => $book->get_series_name(),
		'desc'  => esc_html__( 'Series name', 'book-database' )
	);
	$series_position_args = array(
		'id'    => 'book_series_position',
		'name'  => 'series_position',
		'type'  => 'number',
		'value' => $book->get_series_position(),
		'desc'  => esc_html__( 'Position in the series', 'book-database' )
	);
	?>
	<div id="bookdb-book-series-wrap" class="bookdb-box-row">
		<label><?php _e( 'Series', 'book-database' ); ?></label>
		<div class="bookdb-input-wrapper">
			<div id="bookdb-book-series-name-wrap">
				<?php echo book_database()->html->text( $series_args ); ?>
			</div>

			<div id="bookdb-book-series-position-wrap">
				<?php echo book_database()->html->text( $series_position_args ); ?>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'book-database/book-edit/information-fields', 'bdb_book_series_field' );

// @todo pub field

/**
 * Field: Book Title
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_synopsis_field( $book ) {
	book_database()->html->meta_row( 'textarea', array(
		'label' => __( 'Synopsis', 'book-database' )
	), array(
		'id'    => 'book_synopsis',
		'name'  => 'synopsis',
		'value' => $book->get_synopsis()
	) );
}

add_action( 'book-database/book-edit/information-fields', 'bdb_book_synopsis_field' );

/*
 * Below: Saving Functions
 */

function bdb_edit_book( $args ) {

}

add_action( 'book-database/books/edit-book', 'bdb_edit_book' );