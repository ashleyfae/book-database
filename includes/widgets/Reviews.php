<?php
/**
 * Reviews Widget
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 * @since     1.1
 */

namespace Book_Database\Widgets;

use Book_Database\Book;
use Book_Database\Rating;
use Book_Database\Review;
use Book_Database\Reviews_Query;

/**
 * Class Reviews
 *
 * @package Book_Database\Widgets
 */
class Reviews extends \WP_Widget {

	/**
	 * Reading_Log constructor.
	 */
	public function __construct() {
		parent::__construct(
			'bdb_reviews',
			__( 'BDB - Reviews', 'book-database' ),
			array( 'description' => __( 'Display a list of recent or upcoming book reviews.', 'book-database' ) )
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

		$reviews = $this->query_reviews( $instance );

		if ( ! empty( $reviews ) ) {
			$this->display_reviews( $reviews, $instance );
		} else {
			echo wpautop( __( 'No reviews found.', 'book-database' ) );
		}

		echo $args['after_widget'] ?? '';

	}

	/**
	 * Queries for reviews
	 *
	 * @param array $args
	 *
	 * @return object[]
	 */
	protected function query_reviews( $args ) {

		$query_args = array(
			'number'       => $args['number'] ?? 5,
			'review_query' => array(
				array(
					'field'    => 'date_published',
					'value'    => null,
					'operator' => 'IS NOT'
				)
			)
		);

		if ( 'recent' === $args['status'] ) {
			// Recent reviews
			$query_args['orderby'] = 'review.date_published';
			$query_args['order']   = 'DESC';
		} else {
			// Upcoming reviews
			$query_args['orderby'] = 'review.date_written';
			$query_args['order']   = 'ASC';

			$query_args['review_query'][] = array(
				'field' => 'date_published',
				'value' => array(
					'after' => date( 'Y-m-d H:i:s' )
				)
			);
		}

		if ( ! empty( $args['user_id'] ) ) {
			$query_args['review_query'][] = array(
				'field' => 'user_id',
				'value' => absint( $args['user_id'] )
			);
		}

		$query = new Reviews_Query();

		return $query->get_reviews( $query_args );

	}

	/**
	 * Displays results
	 *
	 * @param object[] $reviews
	 * @param array    $args
	 */
	protected function display_reviews( $reviews, $args ) {
		?>
		<div class="<?php echo count( $reviews ) > 1 ? 'bdb-book-grid bdb-book-grid-col-2 ' : ''; ?>bdb-reviews-widget">
			<?php foreach ( $reviews as $review_data ) :
				$review = new Review( $review_data );

				$book = new Book( array(
					'id'              => $review_data->book_id ?? 0,
					'cover_id'        => $review_data->book_cover_id ?? null,
					'title'           => $review_data->book_title ?? '',
					'pub_date'        => $review_data->book_pub_date ?? '',
					'series_position' => $review_data->series_position ?? ''
				) );
				?>
				<div class="<?php echo count( $reviews ) > 1 ? 'book-grid-entry bdb-grid-entry ' : ''; ?>bdb-reviews-widget-entry">
					<?php
					if ( ! empty( $args['show_book_cover'] ) && $book->get_cover_id() ) {
						if ( $review->is_published() && $review->get_permalink() ) {
							echo '<a href="' . esc_url( $review->get_permalink() ) . '">';
						}

						echo $book->get_cover( 'medium' );

						if ( $review->is_published() && $review->get_permalink() ) {
							echo '</a>';
						}
					}

					if ( $review->is_published() && $review->get_permalink() ) {
						echo '<a href="' . esc_url( $review->get_permalink() ) . '">';
					}
					?>
					<span class="bdb-reviews-widget-book-title"><?php printf( esc_html__( '%s by %s', 'book-database' ), $book->get_title(), $review_data->author_name ); ?></span>
					<?php
					if ( $review->is_published() && $review->get_permalink() ) {
						echo '</a>';
					}

					$this->maybe_show_publish_date( $review, $args );
					$this->maybe_show_rating( $review_data, $args );

					if ( ! empty( $args['show_review_button'] ) && $review->is_published() && $review->get_permalink() ) {
						echo '<a href="' . esc_url( $review->get_permalink() ) . '" class="button bdb-read-review-link">' . __( 'Read Review', 'book-database' ) . '</a>';
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Displays the review publish date if enabled
	 *
	 * @param Review $review
	 * @param array  $args
	 */
	protected function maybe_show_publish_date( $review, $args ) {
		if ( empty( $args['show_publish_date'] ) ) {
			return;
		}

		$publish_text = '';

		if ( $review->get_date_published() && $review->is_published() ) {
			$publish_text = sprintf( __( 'Published %s', 'book-database' ), $review->get_date_published( true ) );
		} elseif ( $review->get_date_published() && strtotime( $review->get_date_published() ) > time() ) {
			$publish_text = sprintf( __( 'Coming %s', 'book-database' ), $review->get_date_published( true ) );
		}

		if ( empty( $publish_text ) ) {
			return;
		}
		?>
		<div class="bdb-reviews-widget-publish-date">
			<?php echo esc_html( $publish_text ); ?>
		</div>
		<?php
	}

	/**
	 * Displays rating if enabled
	 *
	 * @param       $data $data
	 * @param array $args
	 */
	protected function maybe_show_rating( $data, $args ) {
		if ( ! empty( $args['rating_format'] ) && ! is_null( $data->rating ) ) {
			$rating = new Rating( $data->rating );
			?>
			<div class="bdb-reviews-widget-rating bdb-rating-<?php echo esc_attr( $rating->format_html_class() ); ?>">
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
			'title'              => '',
			'user_id'            => '',
			'number'             => 5,
			'status'             => 'recent',
			'show_book_cover'    => false,
			'show_publish_date'  => false,
			'show_review_button' => false,
			'rating_format'      => false
		) );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'book-database' ); ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $args['title'] ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( 'Show books reviewed by this user:', 'book-database' ); ?></label>
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
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Maximum number of reviews:', 'book-database' ); ?></label>
			<input type="number" id="<?php echo $this->get_field_id( 'number' ); ?>" class="tiny-text" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo esc_attr( $args['number'] ); ?>">
		</p>

		<p>
			<input type="radio" id="<?php echo $this->get_field_id( 'status_recent' ); ?>" name="<?php echo $this->get_field_name( 'status' ); ?>" value="recent" <?php checked( 'recent', $args['status'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'status_recent' ); ?>"><?php _e( 'Show recently published reviews', 'book-database' ); ?></label>
			<br/>
			<input type="radio" id="<?php echo $this->get_field_id( 'status_upcoming' ); ?>" name="<?php echo $this->get_field_name( 'status' ); ?>" value="upcoming" <?php checked( 'upcoming', $args['status'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'status_upcoming' ); ?>"><?php _e( 'Show upcoming reviews', 'book-database' ); ?></label>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_book_cover' ); ?>" name="<?php echo $this->get_field_name( 'show_book_cover' ); ?>" value="1" <?php checked( ! empty( $args['show_book_cover'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_book_cover' ); ?>"><?php _e( 'Show book cover', 'book-database' ); ?></label>

			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_publish_date' ); ?>" name="<?php echo $this->get_field_name( 'show_publish_date' ); ?>" value="1" <?php checked( ! empty( $args['show_publish_date'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_publish_date' ); ?>"><?php _e( 'Show review publish date', 'book-database' ); ?></label>

			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_review_button' ); ?>" name="<?php echo $this->get_field_name( 'show_review_button' ); ?>" value="1" <?php checked( ! empty( $args['show_review_button'] ) ); ?>>
			<label for="<?php echo $this->get_field_id( 'show_review_button' ); ?>"><?php _e( 'Show "Read Review" button', 'book-database' ); ?></label>
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
			'title'              => ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '',
			'user_id'            => ! empty( $new_instance['user_id'] ) ? absint( $new_instance['user_id'] ) : '',
			'number'             => ! empty( $new_instance['number'] ) ? absint( $new_instance['number'] ) : 5,
			'status'             => isset( $new_instance['status'] ) && 'recent' === $new_instance['status'] ? 'recent' : 'upcoming',
			'show_book_cover'    => ! empty( $new_instance['show_book_cover'] ) ? 1 : '',
			'show_publish_date'  => ! empty( $new_instance['show_publish_date'] ) ? 1 : '',
			'show_review_button' => ! empty( $new_instance['show_review_button'] ) ? 1 : '',
			'rating_format'      => ! empty( $new_instance['rating_format'] ) ? sanitize_text_field( $new_instance['rating_format'] ) : false
		);
	}

}

/**
 * Registers the widget
 */
add_action( 'widgets_init', function () {
	register_widget( '\\Book_Database\\Widgets\\Reviews' );
} );