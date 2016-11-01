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
 * Field: Book Cover
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_cover_field( $book ) {
	$cover_id = $book->get_cover_id();
	$url      = $book->get_cover_url( 'medium' );

	ob_start();

	$style = $url ? '' : 'display: none;';

	echo '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( sprintf( __( 'Book cover for %s', 'book-database' ), $book->get_title() ) ) . '" id="bookdb-cover-image" style="' . esc_attr( $style ) . '">';

	?>
	<div class="bookdb-cover-image-fields" data-image="#bookdb-cover-image" data-image-id="#book_cover_id">
		<button class="button bookdb-upload-image"><?php esc_html_e( 'Upload Image', 'book-database' ); ?></button>
		<button class="button bookdb-remove-image" style="<?php echo ! $cover_id ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'book-database' ); ?></button>
	</div>

	<input type="hidden" id="book_cover_id" name="cover_id" value="<?php echo esc_attr( absint( $cover_id ) ); ?>">
	<?php

	$field = ob_get_clean();

	book_database()->html->meta_row( 'raw', array(
		'label' => __( 'Cover Image', 'book-database' ),
		'field' => $field
	) );
}

add_action( 'book-database/book-edit/information-fields', 'bdb_book_cover_field' );

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
		'value' => $book->get_series_position(),
		'desc'  => esc_html__( 'Position in the series', 'book-database' )
	);
	?>
	<div id="bookdb-book-series-wrap" class="bookdb-box-row">
		<label><?php _e( 'Series', 'book-database' ); ?></label>
		<div class="bookdb-input-wrapper">
			<div id="bookdb-book-series-name-wrap">
				<?php echo book_database()->html->text( $series_args ); ?>
				<?php if ( $book->get_series_id() ) : ?>
					<input type="hidden" name="series_id" value="<?php echo esc_attr( $book->get_series_id() ); ?>">
				<?php endif; ?>
			</div>

			<div id="bookdb-book-series-position-wrap">
				<?php echo book_database()->html->text( $series_position_args ); ?>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'book-database/book-edit/information-fields', 'bdb_book_series_field' );

/**
 * Field: Publication Date
 *
 * @param BDB_Book $book
 *
 * @since 1.0.0
 * @return void
 */
function bdb_book_pub_date_field( $book ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Publication Date', 'book-database' )
	), array(
		'id'    => 'book_pub_date',
		'name'  => 'pub_date',
		'value' => $book->get_formatted_pub_date(),
		'desc'  => esc_html__( 'Format: September 1st 2016', 'book-database' )
	) );
}

add_action( 'book-database/book-edit/information-fields', 'bdb_book_pub_date_field' );

/**
 * Field: Synopsis
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

/**
 * Save Book
 *
 * Triggers after saving a book via Book Reviews > Book Library.
 *
 * @since 1.0.0
 * @return void
 */
function bdb_save_book() {

	$nonce = isset( $_POST['bdb_save_book_nonce'] ) ? $_POST['bdb_save_book_nonce'] : false;

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'bdb_save_book' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to edit books.', 'book-database' ) );
	}

	$book_id = absint( $_POST['book_id'] );

	$book_data = array(
		'ID' => $book_id
	);

	// Title
	if ( isset( $_POST['title'] ) ) {
		$book_data['title'] = $_POST['title'];
	}

	// @todo cover

	// Series Name
	if ( isset( $_POST['series_name'] ) ) {
		$book_data['series_name'] = $_POST['series_name'];
	}

	// Series ID
	if ( isset( $_POST['series_id'] ) ) {
		$book_data['series_id'] = $_POST['series_id'];
	}

	// Series Position
	if ( isset( $_POST['series_position'] ) ) {
		$book_data['series_position'] = $_POST['series_position'];
	}

	// Pub Date
	if ( isset( $_POST['pub_date'] ) ) {
		$book_data['pub_date'] = $_POST['pub_date'];
	}

	// Synopsis
	if ( isset( $_POST['synopsis'] ) ) {
		$book_data['synopsis'] = $_POST['synopsis'];
	}

	// @todo terms and meta

	// Authors - this works, but it should be formatted differently.
	// bdb_set_book_terms( $book_id, array( 'Sierra Simone' ), 'author' );

	$new_book_id = bdb_insert_book( $book_data );

	if ( ! $new_book_id || is_wp_error( $new_book_id ) ) {
		wp_die( __( 'An error occurred while inserting the book information.', 'book-database' ) );
	}

	$edit_url = add_query_arg( array(
		'update-success' => 'true'
	), bdb_get_admin_page_edit_book( absint( $new_book_id ) ) );

	wp_safe_redirect( $edit_url );

	exit;

}

add_action( 'book-database/book/save', 'bdb_save_book' );