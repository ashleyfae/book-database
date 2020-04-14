<?php
/**
 * Book Covers
 *
 * @package   book-database
 * @copyright Copyright (c) 2020, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Analytics;

use function Book_Database\get_books_admin_page_url;
?>
<# _.each( data, function( row ) { #>
<figure>
	<a href="<?php echo esc_url( get_books_admin_page_url( array( 'view' => 'edit' ) ) ); ?>&book_id={{ row.book_id }}">
	<# if ( row.cover_url ) { #>
		<img src="{{ row.cover_url }}" alt="{{ row.book_title_formatted }}">
	<# } #>
	</a>

	<# if ( row.pub_date_formatted ) { #>
	<figcaption>
		<?php _e( 'Published', 'book-database' ); ?> {{ row.pub_date_formatted }}
	</figcaption>
	<# } #>
</figure>
<# } ); #>
