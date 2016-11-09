<?php
/**
 * Modal Template: Review
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="bookdb-book-form bookdb-review-settings-form">
	<h3><?php esc_html_e( 'Review Settings', 'book-database' ); ?></h3>

	<div class="bookdb-box-row">
		<label for="is_book_review">
			<?php echo book_database()->html->checkbox( array(
				'id'   => 'is_book_review',
				'name' => 'is_book_review'
			) ); ?>
			<?php _e( 'Is book review', 'book-database' ); ?>
		</label>
	</div>

	<?php book_database()->html->meta_row( 'rating_dropdown', array( 'label' => __( 'Rating', 'book-database' ) ), array(
		'id'       => 'book_rating',
		'name'     => 'book_rating',
		'selected' => false
	) ); ?>
</div>