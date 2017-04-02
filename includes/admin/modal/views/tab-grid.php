<?php
/**
 * Modal Template: Book Grid
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p><?php _e( 'Choose your filters for the grid. All fields are optional and may be left blank.', 'book-database' ); ?></p>

<div class="bookdb-book-form bookdb-book-grid-form">
	<?php book_database()->html->meta_row( 'text', array( 'label' => esc_html__( 'Author Name', 'book-database' ) ), array(
		'id'   => 'grid_book_author',
		'name' => 'grid_book_author'
	) ); ?>

	<?php book_database()->html->meta_row( 'text', array( 'label' => esc_html__( 'Series Name', 'book-database' ) ), array(
		'id'   => 'grid_book_series',
		'name' => 'grid_book_series'
	) ); ?>

	<div class="bookdb-box-row">
		<label><?php _e( 'Book Published Between', 'book-database' ); ?></label>
		<div class="bookdb-input-wrapper">
			<div class="bookdb-grid-pub-date-start-wrap">
				<input type="text" id="grid_pub_date_start" name="grid_pub_date_start" placeholder="<?php echo esc_attr( date_i18n( 'F jS, Y', strtotime( '1 month ago' ) ) ); ?>">
				<label for="grid_pub_date_start" class="bookdb-description"><?php _e( 'Start date', 'book-database' ); ?></label>
			</div>
			<div class="bookdb-grid-pub-date-end-wrap">
				<input type="text" id="grid_pub_date_end" name="grid_pub_date_end" placeholder="<?php echo esc_attr( date_i18n( 'F jS, Y' ) ); ?>">
				<label for="grid_pub_date_end" class="bookdb-description"><?php _e( 'End date', 'book-database' ); ?></label>
			</div>
		</div>
	</div>

	<div class="bookdb-box-row">
		<label><?php _e( 'Book Reviewed Between', 'book-database' ); ?></label>
		<div class="bookdb-input-wrapper">
			<div class="bookdb-grid-pub-date-start-wrap">
				<input type="text" id="grid_review_date_start" name="grid_review_date_start" placeholder="<?php echo esc_attr( date_i18n( 'F jS, Y', strtotime( '1 month ago' ) ) ); ?>">
				<label for="grid_review_date_start" class="bookdb-description"><?php _e( 'Start date', 'book-database' ); ?></label>
			</div>
			<div class="bookdb-grid-pub-date-end-wrap">
				<input type="text" id="grid_review_date_end" name="grid_review_date_end" placeholder="<?php echo esc_attr( date_i18n( 'F jS, Y' ) ); ?>">
				<label for="grid_review_date_end" class="bookdb-description"><?php _e( 'End date', 'book-database' ); ?></label>
			</div>
		</div>
	</div>

	<?php book_database()->html->meta_row( 'rating_dropdown', array( 'label' => esc_html__( 'Rating', 'book-database' ) ), array(
		'id'              => 'grid_book_rating',
		'name'            => 'grid_book_rating',
		'show_option_all' => esc_html__( 'Any', 'book-database' ),
		'selected'        => 'Any'
	) ); ?>

	<?php foreach ( bdb_get_taxonomies() as $id => $options ) : ?>
		<div class="bookdb-box-row">
			<label><?php echo esc_html( $options['name'] ) ?></label>
			<div class="bookdb-input-wrapper">
				<?php echo book_database()->html->term_dropdown( $id, array(
					'id'    => 'grid_book_' . $id,
					'name'  => 'grid_book_' . $id,
					'data'  => array( 'term-type' => $id ),
					'class' => 'book-grid-term'
				) ); ?>
			</div>
		</div>
	<?php endforeach; ?>

	<?php book_database()->html->meta_row( 'checkbox', array( 'label' => esc_html__( 'Show Ratings', 'book-database' ) ), array(
		'id'   => 'grid_show_ratings',
		'name' => 'grid_show_ratings',
		'desc' => __( 'Check to display ratings.', 'book-database' )
	) ); ?>

	<?php book_database()->html->meta_row( 'checkbox', array( 'label' => esc_html__( 'Show Review Link', 'book-database' ) ), array(
		'id'   => 'grid_show_review_link',
		'name' => 'grid_show_review_link',
		'desc' => __( 'Check to display link to the review.', 'book-database' )
	) ); ?>

	<?php book_database()->html->meta_row( 'checkbox', array( 'label' => esc_html__( 'Show Goodreads Link', 'book-database' ) ), array(
		'id'   => 'grid_show_goodreads_link',
		'name' => 'grid_show_goodreads_link',
		'desc' => __( 'Check to display link to the Goodreads page.', 'book-database' )
	) ); ?>

	<?php book_database()->html->meta_row( 'checkbox', array( 'label' => esc_html__( 'Reviews Only', 'book-database' ) ), array(
		'id'   => 'grid_reviews',
		'name' => 'grid_reviews',
		'desc' => __( 'Check to only include book that have been reviewed.', 'book-database' )
	) ); ?>

	<?php book_database()->html->meta_row( 'text', array( 'label' => esc_html__( 'Specific Book IDs', 'book-database' ) ), array(
		'id'    => 'grid_book_ids',
		'name'  => 'grid_book_ids',
		'desc'  => __( 'Separate ID numbers with commas.', 'book-database' ),
		'value' => ''
	) ); ?>

	<?php book_database()->html->meta_row( 'select', array( 'label' => esc_html__( 'Order By', 'book-database' ) ), array(
		'options'          => bdb_get_allowed_orderby() + array( 'id' => __( 'Book ID', 'book-database' ) ),
		'id'               => 'grid_order_by',
		'name'             => 'grid_order_by',
		'selected'         => 'id',
		'show_option_all'  => false,
		'show_option_none' => false
	) ); ?>

	<?php book_database()->html->meta_row( 'select', array( 'label' => esc_html__( 'Order', 'book-database' ) ), array(
		'options'          => array(
			'ASC'  => esc_html__( 'ASC (1, 2, 3; a, b, c)', 'book-database' ),
			'DESC' => esc_html__( 'DESC (3, 2, 1; c, b, a)', 'book-database' )
		),
		'id'               => 'grid_order',
		'name'             => 'grid_order',
		'selected'         => 'DESC',
		'show_option_all'  => false,
		'show_option_none' => false
	) ); ?>

	<?php book_database()->html->meta_row( 'text', array( 'label' => esc_html__( 'Maximum Results', 'book-database' ) ), array(
		'id'    => 'grid_number',
		'name'  => 'grid_number',
		'value' => '20',
		'type'  => 'number'
	) ); ?>
</div>