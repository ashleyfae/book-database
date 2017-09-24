<?php
/**
 * Owned Editions
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

/**
 * Display owned editions of the book
 *
 * @param BDB_Book $book
 *
 * @since 1.0
 * @return void
 */
function bdb_owned_editions_table( $book ) {

	if ( 0 == $book->ID ) {
		return;
	}

	$owned_editions = $book->get_owned_editions();
	?>
	<div id="bdb-book-editions-list" class="postbox">
		<h2><?php _e( 'Owned Editions', 'book-database' ) ?></h2>
		<div class="inside">
			<table class="wp-list-table widefat fixed posts">
				<thead>
				<tr>
					<th><?php _e( 'ISBN', 'book-database' ); ?></th>
					<th><?php _e( 'Format', 'book-database' ); ?></th>
					<th><?php _e( 'Date Acquired', 'book-database' ); ?></th>
					<th><?php _e( 'Signed', 'book-database' ); ?></th>
					<th><?php _e( 'Actions', 'book-database' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ( $owned_editions ) {
					foreach ( $owned_editions as $edition ) {
						bdb_owned_edition_entry_tr( $edition );
					}

				} else {
					?>
					<tr id="bookdb-no-owned-editions">
						<td colspan="5"><?php _e( 'You don\'t own any copies of this book.', 'book-database' ); ?></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>

			<button type="button" id="bookdb-add-owned-edition" class="button"><?php esc_html_e( 'Add Edition', 'book-database' ); ?></button>

			<div id="bookdb-owned-edition-fields" data-book-id="<?php echo esc_attr( $book->ID ); ?>">
				<?php
				// ISBN
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'ISBN', 'book-database' )
				), array(
					'id'    => 'owned_edition_isbn',
					'name'  => 'owned_edition_isbn',
					'value' => '',
					'desc'  => esc_html__( 'ISBN of the edition.', 'book-database' )
				) );

				// Format
				book_database()->html->meta_row( 'select', array(
					'label' => __( 'Format', 'book-database' )
				), array(
					'id'               => 'owned_edition_format',
					'name'             => 'owned_edition_format',
					'options'          => bdb_get_book_formats(),
					'show_option_all'  => false,
					'show_option_none' => false,
					'desc'             => esc_html__( 'Edition format.', 'book-database' )
				) );

				// Date Acquired
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'Date Acquired', 'book-database' )
				), array(
					'id'    => 'owned_edition_date_acquired',
					'name'  => 'owned_edition_date_acquired',
					'value' => date_i18n( 'j F Y', current_time( 'timestamp' ) ),
					'desc'  => esc_html__( 'Date you acquired the book.', 'book-database' )
				) );

				// Signed
				book_database()->html->meta_row( 'checkbox', array(
					'label' => __( 'Signed', 'book-database' )
				), array(
					'id'    => 'owned_edition_signed',
					'name'  => 'owned_edition_signed',
					'value' => '',
					'desc'  => __( 'Check on if the book is signed.', 'book-database' )
				) );
				?>

				<button type="button" id="bookdb-submit-owned-edition" class="button"><?php esc_html_e( 'Submit', 'book-database' ); ?></button>
			</div>
		</div>
	</div>
	<?php

}

add_action( 'book-database/book-edit/after-information-fields', 'bdb_owned_editions_table' );

/**
 * Format owned edition entry `<tr>`
 *
 * @param object $edition
 *
 * @since 1.0
 * @return void
 */
function bdb_owned_edition_entry_tr( $edition ) {

	if ( ! is_object( $edition ) ) {
		return;
	}

	?>
	<tr data-edition-id="<?php echo esc_attr( $edition->ID ); ?>">
		<td class="bookdb-owned-edition-isbn">
			<div class="bookdb-owned-edition-display-value">
				<?php echo ! empty( $edition->isbn ) ? esc_html( $edition->isbn ) : '&ndash;'; ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<input type="text" value="<?php echo esc_attr( $edition->isbn ); ?>">
			</div>
		</td>
		<td class="bookdb-owned-edition-format">
			<div class="bookdb-owned-edition-display-value">
				<?php echo ! empty( $edition->format ) ? bdb_get_book_format_label( $edition->format ) : '&ndash;' ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<?php
				echo book_database()->html->select( array(
					'id'               => 'owned_edition_format_' . $edition->ID,
					'name'             => 'owned_edition_format_' . $edition->ID,
					'options'          => bdb_get_book_formats(),
					'selected'         => $edition->format ? $edition->format : '-1',
					'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' ),
					'show_option_all'  => false
				) )
				?>
			</div>
		</td>
		<td class="bookdb-owned-edition-date-acquired">
			<div class="bookdb-owned-edition-display-value">
				<?php echo $edition->date_acquired ? bdb_format_mysql_date( $edition->date_acquired ) : '&ndash;' ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<input type="text" value="<?php echo esc_attr( bdb_format_mysql_date( $edition->date_acquired ) ); ?>">
			</div>
		</td>
		<td class="bookdb-owned-edition-signed">
			<div class="bookdb-owned-edition-display-value">
				<?php echo ! empty( $edition->signed ) ? __( 'Yes', 'book-database' ) : '&ndash;'; ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<input type="checkbox" value="1" <?php checked( $edition->signed, 1 ); ?>>
			</div>
		</td>
		<td>
			<button type="button" class="button bookdb-edit-owned-edition"><?php _e( 'Edit', 'book-database' ); ?></button>
			<button type="button" class="button bookdb-delete-owned-edition"><?php _e( 'Remove', 'book-database' ); ?></button>
		</td>
	</tr>
	<?php

}

/**
 * Ajax CB: Add owned edition
 *
 * @since 1.0
 * @return void
 */
function bdb_save_owned_edition() {
	check_ajax_referer( 'book-database', 'nonce' );

	$edition = isset( $_POST['edition'] ) ? $_POST['edition'] : false;

	if ( empty( $edition ) || ! is_array( $edition ) ) {
		wp_send_json_error( __( 'Error: Invalid edition.', 'book-database' ) );
	}

	// Book ID is required.
	if ( empty( $edition['book_id'] ) ) {
		wp_send_json_error( __( 'Error: Book ID is required.', 'book-database' ) );
	}

	// Verify that the book exists.
	if ( ! book_database()->books->exists( $edition['book_id'] ) ) {
		wp_send_json_error( sprintf( __( 'Error: Invalid book ID %s.', 'book-database' ), $edition['book_id'] ) );
	}

	$args = array();

	if ( ! empty( $edition['ID'] ) ) {
		$args['ID'] = absint( $edition['ID'] );
	}

	$args['book_id']       = absint( $edition['book_id'] );
	$args['isbn']          = ! empty( $edition['isbn'] ) ? sanitize_text_field( $edition['isbn'] ) : '';
	$args['format']        = ( ! empty( $edition['format'] ) && array_key_exists( $edition['format'], bdb_get_book_formats() ) ) ? sanitize_text_field( $edition['format'] ) : '';
	$args['date_acquired'] = ! empty( $edition['date_acquired'] ) ? get_gmt_from_date( wp_strip_all_tags( $edition['date_acquired'] ) ) : null;
	$args['signed']        = ! empty( $edition['signed'] ) ? 1 : null;

	$inserted = book_database()->owned_editions->add( $args );

	if ( empty( $inserted ) ) {
		wp_send_json_error( __( 'Error inserting the edition.', 'book-database' ) );
	}

	$owned_edition = book_database()->owned_editions->get_book( $inserted );

	if ( ! $owned_edition ) {
		wp_send_json_error( __( 'Error inserting the edition.', 'book-database' ) );
	}

	ob_start();
	bdb_owned_edition_entry_tr( $owned_edition );
	wp_send_json_success( ob_get_clean() );

	exit;
}

add_action( 'wp_ajax_bdb_save_owned_edition', 'bdb_save_owned_edition' );

/**
 * Ajax CB: Delete owned edition
 *
 * @since 1.0
 * @return void
 */
function bdb_delete_owned_edition() {
	check_ajax_referer( 'book-database', 'nonce' );

	$edition_id = isset( $_POST['edition_id'] ) ? absint( $_POST['edition_id'] ) : false;

	if ( empty( $edition_id ) ) {
		wp_send_json_error( __( 'Error: Invalid edition ID.', 'book-database' ) );
	}

	$success = book_database()->owned_editions->delete( $edition_id );

	if ( $success ) {
		wp_send_json_success();
	}

	wp_send_json_error( __( 'Error deleting the edition.', 'book-database' ) );

	exit;
}

add_action( 'wp_ajax_bdb_delete_owned_edition', 'bdb_delete_owned_edition' );