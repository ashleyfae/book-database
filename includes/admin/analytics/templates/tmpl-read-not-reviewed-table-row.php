<?php
/**
 * Analytics Template: Books Read But Not Reviewed
 *
 * @package   nosegraze
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
	<td data-th="<?php esc_attr_e('Date Finished', 'book-database'); ?>">
		{{ data.date_finished_formatted }}
		<# if ( data.percentage_complete < 1 ) { #>
		<span class="bdb-rating bdb-dnf"><?php _e( '(DNF)', 'book-database' ); ?></span>
		<# } #>
	</td>
</tr>
