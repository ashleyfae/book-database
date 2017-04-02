<?php
/**
 * Reading Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reading List Table
 *
 * @param BDB_Book $book
 *
 * @since 1.1.0
 * @return void
 */
function bdb_book_reading_list_table( $book ) {
	if ( 0 == $book->ID ) {
		return;
	}

	$entries = bdb_get_book_reading_list( $book->ID, array(
		'orderby' => 'date_started',
		'order'   => 'ASC'
	) );
	?>
    <div id="bdb-book-reading-list" class="postbox">
        <h2><?php esc_html_e( 'Reading Log', 'book-database' ) ?></h2>
        <div class="inside">
            <table class="wp-list-table widefat fixed posts">
                <thead>
                <tr>
                    <th><?php _e( 'Date Started', 'book-database' ); ?></th>
                    <th><?php _e( 'Date Finished', 'book-database' ); ?></th>
                    <th><?php _e( 'Review ID', 'book-database' ); ?></th>
                    <th><?php _e( 'User ID', 'book-database' ); ?></th>
                    <th><?php _e( '% Complete', 'book-database' ); ?></th>
                    <th><?php _e( 'Rating', 'book-database' ); ?></th>
                    <th><?php _e( 'Actions', 'book-database' ); ?></th>
                </tr>
                </thead>
                <tbody>
				<?php
				if ( $entries ) {
					foreach ( $entries as $entry ) {
						bdb_reading_entry_tr( $entry );
					}

				} else {
					?>
                    <tr id="bookdb-no-reading-list-entries">
                        <td colspan="5"><?php _e( 'You haven\'t read this book yet!', 'book-database' ); ?></td>
                    </tr>
					<?php
				}
				?>
                </tbody>
            </table>

            <button type="button" id="bookdb-read-book" class="button"><?php esc_html_e( 'Read Book', 'book-database' ); ?></button>

            <div id="bookdb-read-book-fields" data-book-id="<?php echo esc_attr( $book->ID ); ?>">
				<?php
				// Start Date
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'Start Date', 'book-database' )
				), array(
					'id'    => 'reading_start_date',
					'name'  => 'reading_start_date',
					'value' => date_i18n( 'j F Y' ),
					'desc'  => esc_html__( 'Date you started reading the book.', 'book-database' )
				) );

				// End Date
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'Finish Date', 'book-database' )
				), array(
					'id'    => 'reading_end_date',
					'name'  => 'reading_end_date',
					'value' => date_i18n( 'j F Y', current_time( 'timestamp' ) ),
					'desc'  => esc_html__( 'Date you finished reading the book.', 'book-database' )
				) );

				// User ID
				$current_user = wp_get_current_user();
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'User ID', 'book-database' )
				), array(
					'id'    => 'reading_user_id',
					'name'  => 'reading_user_id',
					'value' => $current_user->ID,
					'type'  => 'number',
					'desc'  => __( 'Default is your user ID.', 'book-database' )
				) );

				// Review ID
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'Review ID', 'book-database' )
				), array(
					'id'    => 'review_id',
					'name'  => 'review_id',
					'value' => '',
					'type'  => 'number',
					'desc'  => __( 'If there\'s a review connected to this read, enter the ID here. Or you can add it later.', 'book-database' )
				) );

				// % Complete
				book_database()->html->meta_row( 'text', array(
					'label' => __( '% Complete', 'book-database' )
				), array(
					'id'    => 'percent_complete',
					'name'  => 'percent_complete',
					'value' => 100,
					'type'  => 'number',
					'desc'  => __( 'Percentage of the book you\'ve read.', 'book-database' )
				) );

				// Rating
				book_database()->html->meta_row( 'rating_dropdown', array( 'label' => __( 'Rating', 'book-database' ) ), array(
					'id'               => 'book_rating',
					'name'             => 'book_rating',
					'selected'         => '-1',
					'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' )
				) );
				?>

                <button type="button" id="bookdb-submit-reading-entry" class="button"><?php esc_html_e( 'Submit', 'book-database' ); ?></button>
            </div>
        </div>
    </div>
	<?php
}

add_action( 'book-database/book-edit/after-information-fields', 'bdb_book_reading_list_table' );

/**
 * Format Reading Entry `<tr>`
 *
 * @param object $entry
 *
 * @since 1.1.0
 * @return void
 */
function bdb_reading_entry_tr( $entry ) {
	if ( ! is_object( $entry ) ) {
		return;
	}
	?>
    <tr data-entry-id="<?php echo esc_attr( $entry->ID ); ?>">
        <td class="bookdb-reading-list-date-started">
            <div class="bookdb-reading-list-display-value">
				<?php echo $entry->date_started ? bdb_format_mysql_date( $entry->date_started ) : '&ndash;'; ?>
            </div>

            <div class="bookdb-reading-list-edit-value">
                <input type="text" value="<?php echo esc_attr( bdb_format_mysql_date( $entry->date_started ) ); ?>">
            </div>
        </td>
        <td class="bookdb-reading-list-date-finished">
            <div class="bookdb-reading-list-display-value">
				<?php echo $entry->date_finished ? bdb_format_mysql_date( $entry->date_finished ) : '&ndash;' ?>
            </div>

            <div class="bookdb-reading-list-edit-value">
                <input type="text" value="<?php echo esc_attr( bdb_format_mysql_date( $entry->date_finished ) ); ?>">
            </div>
        </td>
        <td class="bookdb-reading-list-review-id">
            <div class="bookdb-reading-list-display-value">
				<?php
				if ( $entry->review_id ) {
					echo '<a href="' . esc_url( bdb_get_admin_page_edit_review( absint( $entry->review_id ) ) ) . '">' . sprintf( __( '%d (Edit)', 'book-database' ), absint( $entry->review_id ) ) . '</a>';
				} else {
					$url = add_query_arg( array( 'reading-log' => absint( $entry->ID ) ), bdb_get_admin_page_add_review( $entry->book_id ) );
					echo '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Add Review', 'book-database' ) . '</a>';
				}
				?>
            </div>

            <div class="bookdb-reading-list-edit-value">
				<?php $review_id = ! empty( $entry->review_id ) ? absint( $entry->review_id ) : ''; ?>
                <input type="number" value="<?php echo esc_attr( $review_id ); ?>">
            </div>
        </td>
        <td class="bookdb-reading-list-user-id">
            <div class="bookdb-reading-list-display-value">
				<?php echo absint( $entry->user_id ); ?>
            </div>

            <div class="bookdb-reading-list-edit-value">
                <input type="number" value="<?php echo esc_attr( $entry->user_id ); ?>">
            </div>
        </td>
        <td class="bookdb-reading-list-complete">
            <div class="bookdb-reading-list-display-value">
				<?php echo absint( $entry->complete ); ?>%
            </div>

            <div class="bookdb-reading-list-edit-value">
                <input type="number" value="<?php echo esc_attr( $entry->complete ); ?>">
            </div>
        </td>
        <td class="bookdb-reading-list-rating">
            <div class="bookdb-reading-list-display-value">
				<?php
				$rating = new BDB_Rating( $entry->rating );
				echo $rating->format( 'text' );
				?>
            </div>

            <div class="bookdb-reading-list-edit-value">
				<?php
				echo book_database()->html->rating_dropdown( array(
					'id'               => 'book_rating_' . $entry->ID,
					'name'             => 'book_rating_' . $entry->ID,
					'selected'         => $entry->rating ? $entry->rating : '-1',
					'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' )
				) );
				?>
            </div>
        </td>
        <td>
            <button type="button" class="button bookdb-edit-reading-entry"><?php _e( 'Edit', 'book-database' ); ?></button>
            <button type="button" class="button bookdb-delete-reading-entry"><?php _e( 'Remove', 'book-database' ); ?></button>
        </td>
    </tr>
	<?php
}

/**
 * Ajax CB: Add Reading Entry
 *
 * @since 1.1.0
 * @return void
 */
function bdb_save_reading_entry() {
	check_ajax_referer( 'book-database', 'nonce' );

	$entry = isset( $_POST['entry'] ) ? $_POST['entry'] : false;

	if ( empty( $entry ) ) {
		wp_send_json_error( __( 'Error: Invalid entry.', 'book-database' ) );
	}

	$inserted = bdb_insert_reading_entry( $entry );

	if ( false == $inserted ) {
		wp_send_json_error( __( 'Error inserting the reading entry.', 'book-database' ) );
	}

	$reading_entry = book_database()->reading_list->get_entry( $inserted );

	if ( ! $reading_entry ) {
		wp_send_json_error( __( 'Error inserting the reading entry.', 'book-database' ) );
	}

	ob_start();
	bdb_reading_entry_tr( $reading_entry );
	wp_send_json_success( ob_get_clean() );

	exit;
}

add_action( 'wp_ajax_bdb_save_reading_entry', 'bdb_save_reading_entry' );

/**
 * Ajax CB: Delete Reading Entry
 *
 * @since 1.1.0
 * @return void
 */
function bdb_delete_reading_entry() {
	check_ajax_referer( 'book-database', 'nonce' );

	$entry_id = isset( $_POST['entry_id'] ) ? absint( $_POST['entry_id'] ) : false;

	if ( empty( $entry_id ) ) {
		wp_send_json_error( __( 'Error: Invalid entry ID.', 'book-database' ) );
	}

	$success = book_database()->reading_list->delete( $entry_id );

	if ( $success ) {
		wp_send_json_success();
	}

	wp_send_json_error( __( 'Error deleting the reading entry.', 'book-database' ) );

	exit;
}

add_action( 'wp_ajax_bdb_delete_reading_entry', 'bdb_delete_reading_entry' );