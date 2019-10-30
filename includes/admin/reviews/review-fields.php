<?php
/**
 * Admin Review Fields
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Admin\Reviews\Fields;

use Book_Database\Book_Layout;
use Book_Database\Rating;
use Book_Database\Reading_Log;
use Book_Database\Review;
use function Book_Database\book_database;
use function Book_Database\get_book;
use function Book_Database\get_books_admin_page_url;
use function Book_Database\get_reading_log;
use function Book_Database\get_reading_log_by;
use function Book_Database\get_reading_logs;

/**
 * Field: book ID
 *
 * @param Review|false $review
 */
function book_id( $review ) {

	$book_id = ! empty( $review ) ? $review->get_book_id() : '';

	if ( empty( $book_id ) && ! empty( $_GET['book_id'] ) ) {
		$book_id = absint( $_GET['book_id'] );
	}

	ob_start();
	?>
	<input type="number" id="bdb-book-id" name="book_id" value="<?php echo esc_attr( $book_id ); ?>" required="required">
	<p class="description"><?php _e( 'ID of the book this review is of.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Book ID', 'book-database' ),
		'id'    => 'bdb-book-id',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\book_id' );

/**
 * Field: user ID
 *
 * @param Review|false $review
 */
function user_id( $review ) {

	ob_start();
	?>
	<input type="number" id="bdb-review-user-id" name="user_id" value="<?php echo ! empty( $review ) ? esc_attr( $review->get_user_id() ) : esc_attr( get_current_user_id() ); ?>">
	<p class="description"><?php _e( 'ID of the user reviewing the book. Default is your user ID.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'User ID', 'book-database' ),
		'id'    => 'bdb-review-user-id',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\user_id' );

/**
 * Field: post ID
 *
 * @param Review|false $review
 */
function post_id( $review ) {

	ob_start();
	?>
	<input type="number" id="bdb-post-id" name="post_id" value="<?php echo ( ! empty( $review ) && $review->get_post_id() ) ? esc_attr( $review->get_post_id() ) : ''; ?>">
	<p class="description"><?php _e( 'ID of the post containing the review. Leave blank if the review is not in a blog post.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Review Post ID', 'book-database' ),
		'id'    => 'bdb-post-id',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\post_id' );

/**
 * Field: external URL
 *
 * @param Review|false $review
 */
function url( $review ) {

	ob_start();
	?>
	<input type="text" id="bdb-review-url" class="regular-text" name="url" value="<?php echo ! empty( $review ) ? esc_attr( $review->get_url() ) : ''; ?>" placeholder="https://">
	<p class="description"><?php _e( 'Enter a URL to the external review location.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'External Review URL', 'book-database' ),
		'id'    => 'bdb-review-url',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\url' );

/**
 * Field: date written
 *
 * @param Review|false $review
 */
function date_written( $review ) {

	ob_start();
	?>
	<input type="text" id="bdb-review-date-written" class="bdb-datepicker" name="date_written" value="<?php echo ! empty( $review ) ? esc_attr( $review->get_date_written( true, 'Y-m-d H:i:s' ) ) : date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ); ?>">
	<p class="description"><?php _e( 'Date the review was written.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Date Written', 'book-database' ),
		'id'    => 'bdb-review-date-written',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\date_written' );

/**
 * Field: date published
 *
 * @param Review|false $review
 */
function date_published( $review ) {

	ob_start();
	?>
	<input type="text" id="bdb-review-date-published" class="bdb-datepicker" name="date_published" value="<?php echo ! empty( $review ) ? esc_attr( $review->get_date_published( true, 'Y-m-d H:i:s' ) ) : ''; ?>">
	<p class="description"><?php _e( 'Date the review was published on the blog. Leave blank to hide from archive.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Date Published', 'book-database' ),
		'id'    => 'bdb-review-date-published',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\date_published' );

/**
 * Field: review text
 *
 * @param Review|false $review
 */
function review( $review ) {

	ob_start();
	?>
	<textarea id="bdb-review" class="large-textarea" name="review"><?php echo ! empty( $review ) ? esc_textarea( $review->get_review() ) : ''; ?></textarea>
	<p class="description"><?php _e( 'Review content. This is not displayed publicly.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Review Text', 'book-database' ),
		'id'    => 'bdb-review',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\review' );

/**
 * Field: reading log
 *
 * @param Review|false $review
 */
function reading_log( $review ) {

	$selected_reading_log_id = ! empty( $review ) ? $review->get_reading_log_id() : 0;
	if ( empty( $selected_reading_log ) && ! empty( $_GET['reading_log_id'] ) ) {
		$selected_reading_log_id = absint( $_GET['reading_log_id'] );
	}

	$args = array(
		'number' => 50
	);

	if ( ! empty( $review ) ) {
		$args['book_id'] = $review->get_book_id();
	} elseif ( ! empty( $_GET['book_id'] ) ) {
		$args['book_id'] = absint( $_GET['book_id'] );
	}

	$reading_logs = get_reading_logs( $args );

	ob_start();
	?>
	<select id="bdb-review-reading-log" name="reading_log_id">
		<option value="" <?php selected( empty( $selected_reading_log_id ) ); ?>><?php _e( 'None', 'book-database' ); ?></option>
		<?php foreach ( $reading_logs as $reading_log ) : ?>
			<option value="<?php echo esc_attr( $reading_log->get_id() ); ?>" <?php selected( $selected_reading_log_id, $reading_log->get_id() ); ?>>
				<?php
				$rating = new Rating( $reading_log->get_rating() );
				printf( '%s - %s (%s)', $reading_log->get_date_started( true ), $reading_log->get_date_finished( true ), $rating->format_text() );
				?>
			</option>
		<?php endforeach; ?>
	</select>
	<p class="description"><?php _e( 'Select the reading log associated with this review. This is where the rating comes from.', 'book-database' ); ?></p>
	<?php

	book_database()->get_html()->meta_row( array(
		'label' => __( 'Associated Reading Log', 'book-database' ),
		'id'    => 'bdb-review-reading-log-id',
		'field' => ob_get_clean()
	) );

}


add_action( 'book-database/review-edit/fields', __NAMESPACE__ . '\reading_log' );

/**
 * Associated book information
 *
 * @param Review|false $review
 */
function book_info( $review ) {

	if ( ! $review instanceof Review ) {
		return;
	}

	$reading_log   = get_reading_log_by( 'review_id', $review->get_id() );
	$book          = get_book( $review->get_book_id() );
	$edit_book_url = get_books_admin_page_url( array( 'view' => 'edit', 'book_id' => $book->get_id() ) );
	?>
	<div class="postbox">
		<h2><?php _e( 'Book Information', 'book-database' ); ?></h2>
		<div class="inside">
			<div id="bdb-book-associated-with-review">
				<?php
				$layout = new Book_Layout( $book );

				if ( ! empty( $reading_log ) ) {
					$layout->set_rating( $reading_log->get_rating() );
				}

				echo $layout->get_html();
				?>
				<a href="<?php echo esc_url( $edit_book_url ); ?>" class="button"><?php _e( 'Edit book in admin panel', 'book-database' ); ?></a>
			</div>
		</div>
	</div>
	<?php

}

add_action( 'book-database/review-edit/after-fields', __NAMESPACE__ . '\book_info' );