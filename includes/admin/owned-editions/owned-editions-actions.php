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

	$temp_all_terms = bdb_get_terms( array(
		'number'  => - 1,
		'type'    => 'source',
		'fields'  => 'names',
		'orderby' => 'name',
		'order'   => 'ASC'
	) );
	$all_terms      = array();

	if ( ! is_array( $temp_all_terms ) ) {
		$temp_all_terms = array();
	}

	foreach ( $temp_all_terms as $term_name ) {
		$all_terms[ $term_name ] = $term_name;
	}

	$checks = book_database()->html->multicheck( array(
		'id'      => 'owned_edition_source',
		'name'    => 'owned_edition_source',
		'current' => array(),
		'choices' => $all_terms
	) );
	?>
	<div id="bdb-book-editions-list" class="postbox">
		<h2><?php _e( 'Owned Editions', 'book-database' ) ?></h2>
		<div class="inside">
			<table class="wp-list-table widefat fixed posts">
				<thead>
				<tr>
					<th class="column-primary"><?php _e( 'ISBN', 'book-database' ); ?></th>
					<th><?php _e( 'Format', 'book-database' ); ?></th>
					<th><?php _e( 'Date Acquired', 'book-database' ); ?></th>
					<th><?php _e( 'Source', 'book-database' ); ?></th>
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
					<tr id="bookdb-no-owned-editions" class="no-items">
						<td colspan="6"><?php _e( 'You don\'t own any copies of this book.', 'book-database' ); ?></td>
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

				// Source
				?>
				<div class="bookdb-box-row">
					<label for="owned_edition_source"><?php _e( 'Source', 'book-database' ); ?></label>
					<div class="bookdb-input-wrapper">
						<div id="dbd-checkboxes-source" class="bookdb-taxonomy-checkboxes" data-type="source" data-name="owned_edition_source[]">
							<div class="bookdb-checkbox-wrap">
								<?php echo $checks; ?>
							</div>
							<div class="bookdb-new-checkbox-term">
								<label for="bookdb-new-checkbox-term-source" class="screen-reader-text"><?php esc_html_e( 'Enter the name of a new source', 'book-database' ); ?></label>
								<input type="text" id="bookdb-new-checkbox-term-source" name="bookdb-new-term" class="regular-text bookdb-new-checkbox-term-value">
								<input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>">
							</div>
						</div>
					</div>
				</div>
				<?php

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
		<td class="bookdb-owned-edition-isbn column-primary" data-colname="<?php esc_attr_e( 'ISBN', 'book-database' ); ?>">
			<div class="bookdb-owned-edition-display-value">
				<?php echo ! empty( $edition->isbn ) ? esc_html( $edition->isbn ) : '&ndash;'; ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<input type="text" value="<?php echo esc_attr( $edition->isbn ); ?>">
			</div>

			<button type="button" class="toggle-row">
				<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
			</button>
		</td>
		<td class="bookdb-owned-edition-format" data-colname="<?php esc_attr_e( 'Format', 'book-database' ); ?>">
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
				) );
				?>
			</div>
		</td>
		<td class="bookdb-owned-edition-date-acquired" data-colname="<?php esc_attr_e( 'Date Acquired', 'book-database' ); ?>">
			<div class="bookdb-owned-edition-display-value">
				<?php echo $edition->date_acquired ? bdb_format_mysql_date( $edition->date_acquired ) : '&ndash;' ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<input type="text" value="<?php echo esc_attr( bdb_format_mysql_date( $edition->date_acquired ) ); ?>">
			</div>
		</td>
		<td class="bookdb-owned-edition-source" data-colname="<?php esc_attr_e( 'Source', 'book-database' ); ?>">
			<div class="bookdb-owned-edition-display-value">
				<?php
				if ( ! empty( $edition->source ) ) {
					echo book_database()->book_terms->get_column( 'name', $edition->source );
				} else {
					_e( 'Unknown', 'book-database' );
				}
				?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<?php
				echo book_database()->html->select( array(
					'id'               => 'owned_edition_source_' . $edition->ID,
					'name'             => 'owned_edition_source_' . $edition->ID,
					'options'          => bdb_get_book_sources(),
					'selected'         => ! empty( $edition->source ) ? absint( $edition->source ) : '-1',
					'show_option_none' => _x( 'Unknown', 'no dropdown items', 'book-database' ),
					'show_option_all'  => false
				) );
				?>
			</div>
		</td>
		<td class="bookdb-owned-edition-signed" data-colname="<?php esc_attr_e( 'Signed', 'book-database' ); ?>">
			<div class="bookdb-owned-edition-display-value">
				<?php echo ! empty( $edition->signed ) ? __( 'Yes', 'book-database' ) : '&ndash;'; ?>
			</div>

			<div class="bookdb-owned-edition-edit-value">
				<input type="checkbox" value="1" <?php checked( $edition->signed, 1 ); ?>>
			</div>
		</td>
		<td data-colname="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
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
	$args['source']        = null; // Default to null, override below.
	$args['signed']        = ! empty( $edition['signed'] ) ? 1 : null;

	// Source
	if ( is_numeric( $edition['source'] ) && $edition['source'] > 0 ) {
		$args['source'] = absint( $edition['source'] );
	} elseif ( ! empty( $edition['source'] ) && '-1' != $edition['source'] ) {
		// See if a term with this name already exists.
		$terms = bdb_get_terms( array(
			'name'   => sanitize_text_field( $edition['source'] ),
			'type'   => 'source',
			'fields' => 'ids'
		) );

		if ( ! empty( $terms ) && isset( $terms[0] ) ) {
			$term_id = $terms[0];
		} else {
			// Need to add a new book term.
			$term_id = book_database()->book_terms->add( array(
				'type' => 'source',
				'name' => sanitize_text_field( $edition['source'] ),
			) );
		}

		if ( ! empty( $term_id ) ) {
			$args['source'] = absint( $term_id );
		}
	}

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