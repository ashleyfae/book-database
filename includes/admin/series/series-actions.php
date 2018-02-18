<?php
/**
 * Series Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 * @since     1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Below: Series Fields
 */

/**
 * Field: Series Title
 *
 * @param BDB_Series $series
 *
 * @since 1.0
 * @return void
 */
function bdb_series_name_field( $series ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Name', 'book-database' )
	), array(
		'id'    => 'series_name',
		'name'  => 'name',
		'value' => $series->name
	) );
}

add_action( 'book-database/series-edit/fields', 'bdb_series_name_field' );

/**
 * Field: Series Description
 *
 * @param BDB_Series $series
 *
 * @since 1.0
 * @return void
 */
function bdb_series_desc_field( $series ) {
	book_database()->html->meta_row( 'textarea', array(
		'label' => __( 'Description', 'book-database' )
	), array(
		'id'    => 'series_desc',
		'name'  => 'description',
		'value' => $series->description
	) );
}

add_action( 'book-database/series-edit/fields', 'bdb_series_desc_field' );

/**
 * Field: Series Number of Books
 *
 * @param BDB_Series $series
 *
 * @since 1.0
 * @return void
 */
function bdb_series_number_books_field( $series ) {
	book_database()->html->meta_row( 'text', array(
		'label' => __( 'Total Books', 'book-database' )
	), array(
		'id'    => 'series_number_books',
		'name'  => 'number_books',
		'value' => $series->get_number_books(),
		'type'  => 'number'
	) );
}

add_action( 'book-database/series-edit/fields', 'bdb_series_number_books_field' );

/**
 * Box: Books in this series
 *
 * @param BDB_Series $series
 *
 * @since 1.0
 * @return void
 */
function bdb_series_books_in_series_field( $series ) {

	if ( empty( $series->ID ) ) {
		return;
	}

	$books = $series->get_books();

	if ( empty( $books ) || ! is_array( $books ) ) {
		return;
	}

	?>
	<div class="postbox bdb-books-in-series">
		<h2 class="hndle ui-sortable handle"><?php printf( __( '%s Series', 'book-database' ), esc_html( $series->name ) ); ?></h2>
		<div class="inside">
			<?php
			$average_rating = $series->get_average_rating();

			if ( ! empty( $average_rating ) ) {
				$rating = new BDB_Rating( $average_rating );
				echo '<p>' . sprintf( __( 'Average Rating: %s', 'book-database' ), $rating->format_text() ) . '</p>';
			}
			?>

			<div class="bdb-books-in-series-wrap">
				<?php
				$cover = array( 150, 300 );
				foreach ( $books as $book ) {
					echo '<a href="' . esc_url( admin_url( 'admin.php?page=bdb-books&view=edit&ID=' . absint( $book->ID ) ) ) . '">' . $book->get_cover( $cover ) . '</a>';
				}
				?>
			</div>
		</div>
	</div>
	<?php

}

add_action( 'book-database/series-edit/after-save-box', 'bdb_series_books_in_series_field' );

/*
 * Below: Saving Functions
 */

/**
 * Save Series
 *
 * Triggers after saving a series via Book Library > Book Series.
 *
 * @since 1.0
 * @return void
 */
function bdb_save_series() {

	$nonce = isset( $_POST['bdb_save_series_nonce'] ) ? $_POST['bdb_save_series_nonce'] : false;

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'bdb_save_series' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to edit series.', 'book-database' ) );
	}

	$series_id = absint( $_POST['series_id'] );

	$series_data = array(
		'ID' => $series_id
	);

	// Name
	if ( isset( $_POST['name'] ) ) {
		$series_data['name'] = sanitize_text_field( $_POST['name'] );
	}

	// Description
	if ( isset( $_POST['description'] ) ) {
		$series_data['description'] = wp_kses_post( $_POST['description'] );
	}

	// Number of Books
	if ( isset( $_POST['number_books'] ) ) {
		$series_data['number_books'] = absint( $_POST['number_books'] );
	}

	$updated = book_database()->series->update( $series_id, $series_data );

	if ( ! $updated || is_wp_error( $updated ) ) {
		wp_die( __( 'An error has occurred while inserting the series information.', 'book-database' ) );
	}

	$edit_url = add_query_arg( array(
		'bdb-message' => 'series-updated'
	), bdb_get_admin_page_edit_series( absint( $series_id ) ) );

	wp_safe_redirect( $edit_url );

	exit;

}

add_action( 'book-database/series/save', 'bdb_save_series' );

/**
 * Delete Series
 *
 * Processes deletions from the delete series URL.
 * @see   bdb_get_admin_page_delete_series()
 *
 * @since 1.0
 * @return void
 */
function bdb_delete_series_via_url() {
	if ( ! isset( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'bdb_delete_series' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! isset( $_GET['ID'] ) ) {
		wp_die( __( 'Missing series ID.', 'book-database' ) );
	}

	bdb_delete_series( absint( $_GET['ID'] ) );

	$url = add_query_arg( array(
		'bdb-message' => 'series-deleted'
	), bdb_get_admin_page_series() );

	wp_safe_redirect( $url );

	exit;
}

add_action( 'book-database/series/delete', 'bdb_delete_series_via_url' );