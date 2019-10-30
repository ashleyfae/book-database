<?php
/**
 * Analytics Template: Reviews Written
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

?>
<tr>
	<td data-th="<?php esc_attr_e('Rating', 'book-database'); ?>">
		<span<# if ( data.rating_class ) { #> class="bdb-rating bdb-{{ data.rating_class }}"<# } #>>{{{ data.rating }}}</span>
	</td>
	<td data-th="<?php esc_attr_e('Book', 'book-database'); ?>">
		<a href="<?php echo esc_url( get_books_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&book_id={{ data.book_id }}" title="<?php esc_attr_e( 'Edit book', 'book-database' ); ?>">{{ data.book_title }} <?php _e( 'by', 'book-database' ); ?> {{ data.author_name }}</a>
	</td>
	<td data-th="<?php esc_attr_e('Date Written', 'book-database'); ?>">
		<a href="<?php echo esc_url( get_reviews_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&review_id={{ data.id }}" title="<?php esc_attr_e( 'Edit review', 'book-database' ); ?>">{{ data.date_written_formatted }}</a>
	</td>
</tr>
