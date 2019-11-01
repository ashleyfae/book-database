<?php
/**
 * Analytics Template: Taxonomy Breakdown
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

?>
<tr>
	<td data-colname="<?php esc_attr_e('Name', 'book-database'); ?>">
		{{ data.term_name }}
	</td>
	<td data-colname="<?php esc_attr_e('Books Read', 'book-database'); ?>">
		{{ data.number_books_read }}
	</td>
	<td data-colname="<?php esc_attr_e('Reviews Written', 'book-database'); ?>">
		{{ data.number_reviews }}
	</td>
	<td data-colname="<?php esc_attr_e('Average Rating', 'book-database'); ?>">
		<# if ( null == data.average_rating ) { #>
		&ndash;
		<# } else { #>
		{{{ data.average_rating }}}
		<# } #>
	</td>
</tr>
