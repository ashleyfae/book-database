<?php
/**
 * Review Actions
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Below: Review Fields
 */

/**
 * Field: Associated Book
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_book_id_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Book ID', 'book-database' ) ), array(
		'id'    => 'associated_book',
		'name'  => 'associated_book',
		'value' => isset( $_GET['book_id'] ) ? absint( $_GET['book_id'] ) : $review->get_book_id(),
		'type'  => 'number',
		'desc'  => __( 'The book this is a review of. Enter the book ID number.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_book_id_field' );

/**
 * Field: Associated Post
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_post_id_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Associated Post ID', 'book-database' ) ), array(
		'id'    => 'associated_post',
		'name'  => 'associated_post',
		'value' => $review->get_post_id(),
		'type'  => 'number',
		'desc'  => __( 'Leave blank to not associate this review with a blog post.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_post_id_field' );

/**
 * Field: URL
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_url_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'External Review URL', 'book-database' ) ), array(
		'id'          => 'external_url',
		'name'        => 'external_url',
		'value'       => $review->get_external_url(),
		'type'        => 'url',
		'placeholder' => 'http://',
		'desc'        => __( 'Enter a URL to the external review location. Leave blank if this is a review associated with a blog post.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_url_field' );

/**
 * Field: User ID
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_user_id_field( $review ) {
	$current_user = wp_get_current_user();

	book_database()->html->meta_row( 'text', array( 'label' => __( 'Reviewer User ID', 'book-database' ) ), array(
		'id'    => 'review_user_id',
		'name'  => 'review_user_id',
		'value' => false !== $review->get_user_id() ? $review->get_user_id() : $current_user->ID,
		'type'  => 'number',
		'desc'  => __( 'ID of the user reviewing the book. Default is your user ID.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_user_id_field' );

/**
 * Field: Date Written
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_date_written_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Date Written', 'book-database' ) ), array(
		'id'    => 'review_date',
		'name'  => 'review_date',
		'value' => false !== $review->get_date() ? bdb_format_mysql_date( $review->get_date() ) : '',
		'type'  => 'text',
		'desc'  => __( 'Date the review was written. Leave blank to use today\'s date.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_date_written_field' );

/**
 * Field: Date Published
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_date_published_field( $review ) {
	book_database()->html->meta_row( 'text', array( 'label' => __( 'Date Published', 'book-database' ) ), array(
		'id'    => 'review_date_published',
		'name'  => 'review_date_published',
		'value' => false !== $review->get_date_published() ? bdb_format_mysql_date( $review->get_date_published() ) : '',
		'type'  => 'text',
		'desc'  => __( 'Date the review was published on the blog. Leave blank to hide from archive.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_date_published_field' );

/**
 * Field: Review Content
 *
 * @param BDB_Review $review
 *
 * @since 1.2.2
 * @return void
 */
function bdb_review_text_field( $review ) {
	book_database()->html->meta_row( 'textarea', array( 'label' => __( 'Review Text', 'book-database' ) ), array(
		'id'    => 'review_text',
		'name'  => 'review_text',
		'value' => $review->get_review(),
		'desc'  => __( 'Review content. For your records only.', 'book-database' )
	) );
}

add_action( 'book-database/review-edit/fields', 'bdb_review_text_field' );

/**
 * Field: Insert Reading Log
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_insert_reading_log_field( $review ) {
	$reading_entry = false;

	// Fetch by GET.
	if ( isset( $_GET['reading-log'] ) ) {
		$reading_entry = book_database()->reading_list->get_entry( absint( $_GET['reading-log'] ) );
	}

	// Fetch via database.
	if ( empty( $reading_entry ) ) {
		$reading_entry = bdb_get_review_reading_entry( $review->ID );
	}

	// Get all the entries associated with this book.
	$all_book_entries = book_database()->reading_list->get_entries( array( 'book_id' => $review->book_id ) );
	$choose_entries   = array();

	if ( is_array( $all_book_entries ) ) {
		foreach ( $all_book_entries as $entry ) {
			$rating                       = new BDB_Rating( $entry->rating );
			$choose_entries[ $entry->ID ] = sprintf( '%s - %s (%s)', bdb_format_mysql_date( $entry->date_started ), bdb_format_mysql_date( $entry->date_finished ), $rating->format_text() );
		}
	}

	book_database()->html->meta_row( 'select', array( 'label' => __( 'Associate Reading Log', 'book-database' ) ), array(
		'id'               => 'insert_reading_log',
		'name'             => 'insert_reading_log',
		'selected'         => is_object( $reading_entry ) ? 'existing' : '-1',
		'show_option_all'  => false,
		'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' ),
		'options'          => array(
			'existing' => esc_html__( 'Choose from existing entries', 'book-database' ),
			'create'   => esc_html__( 'Create new entry', 'book-database' )
		)
	) );
	?>
	<div id="bookdb-review-existing-reading-log-fields">
		<?php
		book_database()->html->meta_row( 'select', array( 'label' => __( 'Select Existing Entry', 'book-database' ) ), array(
			'id'               => 'reading_log_id',
			'name'             => 'reading_log_id',
			'selected'         => is_object( $reading_entry ) ? $reading_entry->ID : '-1',
			'show_option_all'  => false,
			'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' ),
			'options'          => $choose_entries
		) );
		?>
	</div>

	<div id="bookdb-review-new-reading-log-fields">
		<?php
		// Start Date
		book_database()->html->meta_row( 'text', array(
			'label' => __( 'Start Date', 'book-database' )
		), array(
			'id'    => 'reading_start_date',
			'name'  => 'reading_start_date',
			'value' => $reading_entry ? bdb_format_mysql_date( $reading_entry->date_started ) : date_i18n( 'j F Y' ),
			'desc'  => esc_html__( 'Date you started reading the book.', 'book-database' )
		) );

		// End Date
		book_database()->html->meta_row( 'text', array(
			'label' => __( 'Finish Date', 'book-database' )
		), array(
			'id'    => 'reading_end_date',
			'name'  => 'reading_end_date',
			'value' => $reading_entry ? bdb_format_mysql_date( $reading_entry->date_finished ) : date_i18n( 'j F Y' ),
			'desc'  => esc_html__( 'Date you finished reading the book.', 'book-database' )
		) );

		// User ID
		$current_user = wp_get_current_user();
		book_database()->html->meta_row( 'text', array(
			'label' => __( 'User ID', 'book-database' )
		), array(
			'id'    => 'reading_user_id',
			'name'  => 'reading_user_id',
			'value' => $reading_entry ? $reading_entry->user_id : $current_user->ID,
			'type'  => 'number',
			'desc'  => __( 'Default is your user ID.', 'book-database' )
		) );

		// Review ID
		book_database()->html->meta_row( 'text', array(
			'label' => __( 'Review ID', 'book-database' )
		), array(
			'id'    => 'review_id',
			'name'  => 'review_id',
			'value' => $review->ID,
			'type'  => 'number',
			'desc'  => __( 'If there\'s a review connected to this read, enter the ID here. Or you can add it later.', 'book-database' )
		) );

		// % Complete
		book_database()->html->meta_row( 'text', array(
			'label' => __( '% Complete', 'book-database' )
		), array(
			'id'    => 'percent_complete',
			'name'  => 'percent_complete',
			'value' => $reading_entry ? $reading_entry->complete : 100,
			'type'  => 'number',
			'desc'  => __( 'Percentage of the book you\'ve read.', 'book-database' )
		) );

		// Rating
		book_database()->html->meta_row( 'rating_dropdown', array( 'label' => __( 'Rating', 'book-database' ) ), array(
			'id'               => 'book_rating',
			'name'             => 'book_rating',
			'selected'         => $reading_entry ? $reading_entry->rating : '-1',
			'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' )
		) );
		?>
	</div>
	<?php
}

add_action( 'book-database/review-edit/fields', 'bdb_review_insert_reading_log_field' );

/**
 * Box: Associated Book Information
 *
 * Displays information about the associated book.
 *
 * @param BDB_Review $review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_review_show_associated_book( $review ) {
	if ( ! $review->book_id ) {
		return;
	}

	$book = new BDB_Book( absint( $review->book_id ) );

	if ( ! $book->ID > 0 ) {
		return;
	}

	?>
	<div class="postbox">
		<h2><?php _e( 'Associated Book', 'book-database' ); ?></h2>
		<div class="inside">
			<?php do_action( 'book-database/review-edit/associated-book/before', $review, $book ); ?>
			<div id="bookdb-book-associated-with-review">
				<?php echo $book->get_formatted_info(); ?>
				<a href="<?php echo esc_url( bdb_get_admin_page_edit_book( $book->ID ) ); ?>" class="button"><?php _e( 'Edit book in admin panel', 'book-database' ); ?></a>
			</div>
			<?php do_action( 'book-database/review-edit/associated-book/after', $review, $book ); ?>
		</div>
	</div>
	<?php
}

add_action( 'book-database/review-edit/after-fields', 'bdb_review_show_associated_book' );

/*
 * Below: Saving Functions
 */

/**
 * Save Review
 *
 * @since 1.0.0
 * @return void
 */
function bdb_save_review() {

	$nonce = isset( $_POST['bdb_save_review_nonce'] ) ? $_POST['bdb_save_review_nonce'] : false;

	if ( ! $nonce ) {
		return;
	}

	if ( ! wp_verify_nonce( $nonce, 'bdb_save_review' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! current_user_can( 'edit_posts' ) ) { // @todo maybe change
		wp_die( __( 'You don\'t have permission to edit reviews.', 'book-database' ) );
	}

	$review_id = absint( $_POST['review_id'] );

	$review_data = array(
		'ID' => $review_id
	);

	$fields = array(
		'book_id' => 'associated_book',
		'post_id' => 'associated_post',
		'url'     => 'external_url',
		'user_id' => 'review_user_id',
		'review'  => 'review_text'
	);

	foreach ( $fields as $db_field => $post_field ) {
		if ( ! isset( $_POST[ $post_field ] ) ) {
			continue;
		}

		$review_data[ $db_field ] = stripslashes( $_POST[ $post_field ] );
	}

	// Format the date written.
	if ( isset( $_POST['review_date'] ) && ! empty( $_POST['review_date'] ) ) {
		$review_data['date_written'] = $_POST['review_date'];
	}

	// Format the date published.
	if ( isset( $_POST['review_date_published'] ) && ! empty( $_POST['review_date_published'] ) ) {
		$review_data['date_published'] = $_POST['review_date_published'];
	} else {
		$review_data['date_published'] = null;
	}

	$new_review_id = bdb_insert_review( apply_filters( 'book-database/review/save/review-data', $review_data, $review_id, $_POST ) );

	if ( ! $new_review_id || is_wp_error( $new_review_id ) ) {
		wp_die( __( 'An error occurred while inserting the review.', 'book-database' ) );
	}

	/*
	 * Maybe save reading log.
	 */
	if ( isset( $_POST['insert_reading_log'] ) && '-1' != $_POST['insert_reading_log'] ) {

		$result = false;

		// Create new log
		if ( 'create' == $_POST['insert_reading_log'] ) {

			$reading_data = array(
				'book_id'       => $review_data['book_id'],
				'review_id'     => $new_review_id,
				'user_id'       => absint( $_POST['reading_user_id'] ),
				'date_started'  => $_POST['reading_start_date'],
				'date_finished' => $_POST['reading_end_date'],
				'complete'      => $_POST['percent_complete'],
				'rating'        => $_POST['book_rating']
			);

			$result = bdb_insert_reading_entry( $reading_data );

		} elseif ( 'existing' == $_POST['insert_reading_log'] && '-1' != $_POST['reading_log_id'] ) {

			$result = book_database()->reading_list->update( absint( $_POST['reading_log_id'] ), array( 'review_id' => $new_review_id ) );

		} else {

			// Find any logs with this review and disassociate.
			$logs = book_database()->reading_list->get_entries( array( 'review_id' => $new_review_id ) );

			if ( is_array( $logs ) && ! empty( $logs ) ) {
				foreach ( $logs as $log ) {
					book_database()->reading_list->update( $log->ID, array( 'review_id' => 0 ) );
				}
			}

			$result = true;

		}

		if ( ! $result || is_wp_error( $result ) ) {
			wp_die( __( 'An error ocurred while inserting the reading data.', 'book-database' ) );
		}

	} else {

		// Find any logs with this review and disassociate.
		$logs = book_database()->reading_list->get_entries( array( 'review_id' => $new_review_id ) );

		if ( is_array( $logs ) && ! empty( $logs ) ) {
			foreach ( $logs as $log ) {
				book_database()->reading_list->update( $log->ID, array( 'review_id' => 0 ) );
			}
		}

	}

	$edit_url = add_query_arg( array(
		'bdb-message' => 'review-updated'
	), bdb_get_admin_page_edit_review( absint( $new_review_id ) ) );

	wp_safe_redirect( $edit_url );

	exit;

}

add_action( 'book-database/review/save', 'bdb_save_review' );

/**
 * Delete Review
 *
 * Processes deletions from the delete review URL.
 * @see   bdb_get_admin_page_delete_review()
 *
 * @since 1.0.0
 * @return void
 */
function bdb_delete_review_via_url() {
	if ( ! isset( $_GET['nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_GET['nonce'], 'bdb_delete_review' ) ) {
		wp_die( __( 'Failed security check.', 'book-database' ) );
	}

	if ( ! isset( $_GET['ID'] ) ) {
		wp_die( __( 'Missing review ID.', 'book-database' ) );
	}

	$result = book_database()->reviews->delete( absint( $_GET['ID'] ) );

	$message = $result ? 'review-deleted' : 'review-delete-failed';
	$url     = add_query_arg( array(
		'bdb-message' => urlencode( $message )
	), bdb_get_admin_page_reviews() );

	wp_safe_redirect( $url );

	exit;
}

add_action( 'book-database/review/delete', 'bdb_delete_review_via_url' );