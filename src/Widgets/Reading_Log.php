<?php
/**
 * Reading Log Widget
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.1
 */

namespace Book_Database\Widgets;

use Book_Database\Book;
use Book_Database\Rating;
use function Book_Database\book_database;
use function Book_Database\get_book;

/**
 * Class Reading_Log
 *
 * @package Book_Database\Widgets
 */
class Reading_Log extends \WP_Widget {

	/**
	 * Reading_Log constructor.
	 */
	public function __construct() {
		parent::__construct(
			'bdb_reading_log',
			__( 'BDB - Reading Log', 'book-database' ),
			array( 'description' => __( 'Display a list of currently or recently read books.', 'book-database' ) )
		);
	}

	/**
	 * Displays the widget on the front-end
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from the database.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'] ?? '';

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$logs = $this->query_reading_logs( $instance );

		if ( ! empty( $logs ) ) {
			$this->display_books( $logs, $instance );
		} else {
			echo wpautop( __( 'No books found.', 'book-database' ) );
		}

		echo $args['after_widget'] ?? '';

	}

	/**
	 * Queries for reading logs
	 *
	 * @param array $args
	 *
	 * @return \Book_Database\Reading_Log[]
	 */
	protected function query_reading_logs( $args ) {

		global $wpdb;

		$tbl_log = book_database()->get_table( 'reading_log' )->get_table_name();

		$user_id = ! empty( $args['user_id'] ) ? $wpdb->prepare( "AND user_id = %d", $args['user_id'] ) : '';
		$order   = 'recent' === $args['status'] ? 'DESC' : 'ASC';

		if ( 'recent' === $args['status'] ) {
			$date_sql = "AND date_started IS NOT null AND date_finished IS NOT NULL";
		} else {
			$date_sql = "AND date_started IS NOT NULL and date_finished IS NULL";
		}

		$query = $wpdb->prepare(
			"SELECT * FROM {$tbl_log}
    			WHERE 1 = 1
				{$user_id}
				{$date_sql}
				ORDER BY date_started {$order}
				LIMIT %d",
			$args['number'] ?? 5
		);

		$results = $wpdb->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $result ) {
				$results[ $key ] = new \Book_Database\Reading_Log( $result );
			}
		}

		return $results;

	}

	/**
	 * Displays results
	 *
	 * @param \Book_Database\Reading_Log[] $logs
	 * @param array                        $args
	 */
	protected function display_books( $logs, $args ) {
		?>
		<div class="<?php echo count( $logs ) > 1 ? 'bdb-book-grid bdb-book-grid-col-2 ' : ''; ?>bdb-reading-log-widget-list">
			<?php foreach ( $logs as $log ) :
				$book = get_book( $log->get_book_id() );

				if ( ! $book instanceof Book ) {
					continue;
				}
				?>
				<div class="<?php echo count( $logs ) > 1 ? 'book-grid-entry bdb-grid-entry ' : ''; ?>bdb-reading-log-widget-entry">
					<?php
					if ( ! empty( $args['show_book_cover'] ) && $book->get_cover_id() ) {
						if ( ! empty( $args['link_goodreads'] ) && $book->get_goodreads_url() ) {
							echo '<a href="' . esc_url( $book->get_goodreads_url() ) . '" target="_blank">';
						}

						echo $book->get_cover( 'medium' );

						if ( ! empty( $args['link_goodreads'] ) && $book->get_goodreads_url() ) {
							echo '</a>';
						}
					}

					if ( ! empty( $args['link_goodreads'] ) && $book->get_goodreads_url() ) {
						echo '<a href="' . esc_url( $book->get_goodreads_url() ) . '" target="_blank">';
					}
					?>
					<span class="bdb-reading-log-book-title"><?php printf( __( '%s by %s', 'book-database' ), $book->get_title(), $book->get_author_names( true ) ); ?></span>
					<?php
					if ( ! empty( $args['link_goodreads'] ) && $book->get_goodreads_url() ) {
						echo '</a>';
					}

					$this->maybe_show_dates( $log, $args );
					$this->maybe_show_rating( $log, $args );
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Displays start and finish dates if enabled
	 *
	 * @param \Book_Database\Reading_Log $log
	 * @param array                      $args
	 */
	protected function maybe_show_dates( $log, $args ) {

		if ( empty( $args['show_start_date'] ) && empty( $args['show_finish_date'] ) ) {
			return;
		}
		?>
		<span class="bdb-reading-log-date">
			<?php if ( ! empty( $args['show_start_date'] ) ) : ?>
				<span class="bdb-reading-log-date-started"><?php echo $log->get_date_started( true ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $args['show_finish_date'] ) ) : ?>
				<?php if ( ! empty( $args['show_start_date'] ) ) : ?>
					<span class="bdb-reading-log-date-separator">&ndash;</span>
				<?php endif; ?>

				<span class="bdb-reading-log-date-finished">
					<?php
					if ( $log->get_date_finished() ) {
						echo $log->get_date_finished( true );
					} else {
						_e( 'Current', 'book-database' );
					}
					?>
				</span>
			<?php endif; ?>
		</span>
		<?php

	}

	/**
	 * Displays rating if enabled
	 *
	 * @param \Book_Database\Reading_Log $log
	 * @param array                      $args
	 */
	protected function maybe_show_rating( $log, $args ) {
		if ( ! empty( $args['rating_format'] ) && ! is_null( $log->get_rating() ) ) {
			$rating = new Rating( $log->get_rating() );
			?>
			<div class="bdb-reading-log-rating bdb-rating-<?php echo esc_attr( $rating->format_html_class() ); ?>">
				<?php echo $rating->format( $args['rating_format'] ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Displays the admin form settings
	 *
	 * @param array $instance Current form settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {

		$args = wp_parse_args( $instance, array(
			'title'            => '',
			'user_id'          => '',
			'number'           => 5,
			'status'           => 'recent',
			'show_book_cover'  => false,
			'show_start_date'  => false,
			'show_finish_date' => false,
			'link_goodreads'   => false,
			'rating_format'    => false
		) );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'book-database' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $args['title'] ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( 'Show books read by this user:', 'book-database' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'user_id' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'user_id' ); ?>">
				<option value="" <?php selected( empty( $args['user_id'] ) ); ?>><?php _e( 'All Users', 'book-database' ); ?></option>
				<?php foreach ( get_users( array( 'role' => 'administrator' ) ) as $user ) :
					$name = $user->user_login;

					if ( get_current_user_id() == $user->ID ) {
						$name .= ' ' . __( '(You!)', 'book-database' );
					}
					?>
					<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $user->ID, $args['user_id'] ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Maximum number of books:', 'book-database' ); ?></label>
			<input type="number" id="<?php echo $this->get_field_id( 'number' ); ?>" class="tiny-text" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo esc_attr( $args['number'] ); ?>">
		</p>

		<p>
			<input type="radio" id="<?php echo $this->get_field_id( 'status_recent' ); ?>" name="<?php echo $this->get_field_name( 'status' ); ?>" value="recent" <?php checked( 'recent', $args['status'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'status_recent' ); ?>"><?php _e( 'Show recently finished books', 'book-database' ); ?></label>
			<br/>
			<input type="radio" id="<?php echo $this->get_field_id( 'status_current' ); ?>" name="<?php echo $this->get_field_name( 'status' ); ?>" value="current" <?php checked( 'current', $args['status'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'status_current' ); ?>"><?php _e( 'Show currently reading', 'book-database' ); ?></label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_book_cover' ); ?>" name="<?php echo $this->get_field_name( 'show_book_cover' ); ?>" value="1" <?php checked( ! empty( $args['show_book_cover'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_book_cover' ); ?>"><?php _e( 'Show book cover', 'book-database' ); ?></label>

			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_start_date' ); ?>" name="<?php echo $this->get_field_name( 'show_start_date' ); ?>" value="1" <?php checked( ! empty( $args['show_start_date'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_start_date' ); ?>"><?php _e( 'Show date started', 'book-database' ); ?></label>

			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_finish_date' ); ?>" name="<?php echo $this->get_field_name( 'show_finish_date' ); ?>" value="1" <?php checked( ! empty( $args['show_finish_date'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_finish_date' ); ?>"><?php _e( 'Show date finished', 'book-database' ); ?></label>

			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'link_goodreads' ); ?>" name="<?php echo $this->get_field_name( 'link_goodreads' ); ?>" value="1" <?php checked( ! empty( $args['link_goodreads'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'link_goodreads' ); ?>"><?php _e( 'Link to Goodreads', 'book-database' ); ?></label>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'rating_format' ); ?>"><?php _e( 'Rating format:', 'book-database' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'rating_format' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'rating_format' ); ?>">
				<option value="" <?php selected( empty( $args['rating_format'] ) ); ?>><?php _e( 'Not Shown', 'book-database' ); ?></option>
				<option value="font_awesome" <?php selected( 'font_awesome', $args['rating_format'] ); ?>><?php _e( 'Font Awesome Stars', 'book-database' ); ?></option>
				<option value="html_stars" <?php selected( 'html_stars', $args['rating_format'] ); ?>><?php _e( 'HTML Stars', 'book-database' ); ?></option>
				<option value="text" <?php selected( 'text', $args['rating_format'] ); ?>><?php _e( 'Plain Text', 'book-database' ); ?></option>
			</select>
		</p>
		<?php

	}

	/**
	 * Sanitizes and saves the form settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'            => ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '',
			'user_id'          => ! empty( $new_instance['user_id'] ) ? absint( $new_instance['user_id'] ) : '',
			'number'           => ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5,
			'status'           => isset( $new_instance['status'] ) && 'recent' === $new_instance['status'] ? 'recent' : 'current',
			'show_book_cover'  => ! empty( $new_instance['show_book_cover'] ) ? 1 : '',
			'show_start_date'  => ! empty( $new_instance['show_start_date'] ) ? 1 : '',
			'show_finish_date' => ! empty( $new_instance['show_finish_date'] ) ? 1 : '',
			'link_goodreads'   => ! empty( $new_instance['link_goodreads'] ) ? 1 : '',
			'rating_format'    => ! empty( $new_instance['rating_format'] ) ? sanitize_text_field( $new_instance['rating_format'] ) : false
		);
	}

}

/**
 * Registers the widget
 */
add_action( 'widgets_init', function () {
	register_widget( '\\Book_Database\\Widgets\\Reading_log' );
} );
