<?php
/**
 * Lowest Rated Books
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\get_books_admin_page_url;
?>
<# _.each( data, function( row ) { #>
<tr>
	<td class="column-primary" data-colname="<?php esc_attr_e( 'Book', 'book-database' ); ?>">
		<a href="<?php echo esc_url( get_books_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&book_id={{ row.book_id }}">
			{{ row.book_title }}
		</a>

		<button type="button" class="toggle-row">
			<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
		</button>
	</td>
	<td data-colname="<?php esc_attr_e( 'Rating', 'book-database' ); ?>">
		{{{ row.rating_formatted }}}
	</td>
	<td data-colname="<?php esc_attr_e( 'Dates Read', 'book-database' ); ?>">
		{{ row.date_started_formatted }} - {{ row.date_finished_formatted }}
	</td>
</tr>
<# } ); #>
