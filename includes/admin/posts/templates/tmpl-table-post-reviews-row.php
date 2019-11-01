<?php
/**
 * Template: Review Associated With Post
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

?>
<tr id="bdb-review-{{ data.id }}" data-id="{{ data.id }}">
	<td data-colname="<?php esc_attr_e( 'ID', 'book-database' ); ?>">
		{{ data.id }} <a href="<?php echo esc_url( get_reviews_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&review_id={{ data.id }}"><?php _e( '(Edit)', 'book-database' ); ?></a>
	</td>

	<td data-colname="<?php esc_attr_e( 'Book', 'book-database' ); ?>">
		<a href="<?php echo esc_url( get_books_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&book_id={{ data.book_id }}" title="<?php esc_attr_e( 'Edit book', 'book-database' ); ?>">
			{{ data.book_title }} <?php _e( 'by', 'book-database' ); ?> {{ data.author_name }}
		</a>
	</td>

	<td data-colname="<?php esc_attr_e( 'Rating', 'book-database' ); ?>">
		<# if ( data.rating_formatted ) { #>
			{{ data.rating_formatted }}
		<# } else { #>
			&ndash;
		<# } #>
	</td>

	<td data-colname="<?php esc_attr_e( 'Shortcode', 'book-database' ); ?>">
		<code>[book id="{{ data.book_id }}"<# if ( data.rating ) { #> rating="{{ data.rating }}"<# } #>]</code>
	</td>

	<td data-colname="<?php esc_attr_e( 'Remove', 'book-database' ); ?>">
		<button type="button" class="button bdb-disassociate-review-from-post"><?php _e( 'Remove from Post', 'book-database' ); ?></button>
		<button type="button" class="button bdb-delete-review"><?php _e( 'Delete', 'book-database' ); ?></button>
	</td>
</tr>