<?php
/**
 * Reading List Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Book Reading List
 *
 * Returns the reading entries for a given book.
 *
 * @param int $book_id
 *
 * @since 1.1.0
 * @return array|false
 */
function bdb_get_book_reading_list( $book_id ) {

	$entries = book_database()->reading_list->get_entry_by( 'book_id', absint( $book_id ) );

	return $entries;

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

	$entries = bdb_get_book_reading_list( $book->ID );
	?>
	<div id="bdb-book-reading-list" class="postbox">
		<h2><?php esc_html_e( 'Reading List', 'book-database' ) ?></h2>
		<div class="inside">
			<?php if ( $entries ) : ?>
				<table class="wp-list-table widefat fixed posts">
					<thead>
					<tr>
						<th><?php _e( 'Date Started', 'book-database' ); ?></th>
						<th><?php _e( 'Date Finished', 'book-database' ); ?></th>
						<th><?php _e( 'Review', 'book-database' ); ?></th>
						<th><?php _e( 'Remove', 'book-database' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ( $entries as $entry ) :
						?>
						<tr>
							<td>

							</td>
							<td>

							</td>
							<td>

							</td>
							<td>

							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php _e( 'You haven\'t read this book yet!', 'book-database' ); ?></p>
			<?php endif; ?>

			<button type="button" class="button"><?php esc_html_e( 'Read Book', 'book-database' ); ?></button>

			<div id="bookdb-read-book-fields">
				<?php
				// Start Date
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'Start Date', 'book-database' )
				), array(
					'id'    => 'reading_start_date',
					'name'  => 'reading_start_date',
					'value' => date( 'j F Y', current_time( 'timestamp' ) ),
					'desc'  => esc_html__( 'Date you started reading the book.', 'book-database' )
				) );

				// End Date
				book_database()->html->meta_row( 'text', array(
					'label' => __( 'Finish Date', 'book-database' )
				), array(
					'id'    => 'reading_end_date',
					'name'  => 'reading_end_date',
					'value' => date( 'j F Y', current_time( 'timestamp' ) ),
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
				?>
			</div>

			<button type="button" id="bookdb-submit-reading-entry" class="button"><?php esc_html_e( 'Submit', 'book-database' ); ?></button>
		</div>
	</div>
	<?php
}

add_action( 'book-database/book-edit/after-information-fields', 'bdb_book_reading_list_table' );