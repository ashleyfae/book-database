<?php
/**
 * Analytics Template: Rating Breakdown
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

?>
<tr>
	<td data-th="<?php esc_attr_e('Rating', 'book-database'); ?>">
		<# if ( 'none' == data.rating ) { #>
			&ndash;
		<# } else { #>
			{{{ data.rating }}}
		<# } #>
	</td>
	<td data-th="<?php esc_attr_e('Number of Books', 'book-database'); ?>">
		{{ data.number_books }}
	</td>
</tr>
