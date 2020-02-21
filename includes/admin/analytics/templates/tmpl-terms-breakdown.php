<?php
/**
 * Terms Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;
?>
<# _.each( data, function( row ) { #>
<tr>
	<td class="column-primary" data-colname="<?php esc_attr_e( 'Name', 'book-database' ); ?>">
		{{ row.term_name }}

		<button type="button" class="toggle-row">
			<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
		</button>
	</td>
	<td data-colname="<?php esc_attr_e( 'Books Read', 'book-database' ); ?>">
		{{ row.number_books_read }}
	</td>
	<td data-colname="<?php esc_attr_e( 'Reviews Written', 'book-database' ); ?>">
		{{ row.number_reviews }}
	</td>
	<td data-colname="<?php esc_attr_e( 'Average Rating', 'book-database' ); ?>">
		{{{ row.average_rating_formatted }}}
	</td>
</tr>
<# } ); #>
