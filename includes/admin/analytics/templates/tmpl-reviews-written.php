<?php
/**
 * Reviews Written
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\get_books_admin_page_url;
use function Book_Database\get_reviews_admin_page_url;
?>
<# _.each( data, function( row ) { #>
<tr>
	<td class="column-primary" data-colname="<?php esc_attr_e( 'Date Written', 'book-database' ); ?>">
		<a href="<?php echo esc_url( get_reviews_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&review_id={{ row.review_id }}">
			{{ row.date_written_formatted }}
		</a>

		<button type="button" class="toggle-row">
			<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
		</button>
	</td>
	<td data-colname="<?php esc_attr_e( 'Book', 'book-database' ); ?>">
		<a href="<?php echo esc_url( get_books_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&book_id={{ row.book_id }}">
			{{ row.book_title_formatted }}
		</a>
	</td>
	<td data-colname="<?php esc_attr_e( 'Rating', 'book-database' ); ?>">
		<span class="bdb-rating bdb-{{ row.rating_class }}">{{{ row.rating_formatted }}}</span>
	</td>
</tr>
<# } ); #>
