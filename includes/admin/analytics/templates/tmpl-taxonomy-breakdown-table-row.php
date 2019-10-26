<?php
/**
 * Analytics Template: Taxonomy Breakdown
 *
 * @package   nosegraze
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

?>
<tr>
	<td data-th="<?php esc_attr_e('Name', 'book-database'); ?>">
		{{ data.term_name }}
	</td>
	<td data-th="<?php esc_attr_e('Books Read', 'book-database'); ?>">
		{{ data.number_books }}
	</td>
	<td data-th="<?php esc_attr_e('Reviews Written', 'book-database'); ?>">
		{{ data.number_reviews }}
	</td>
	<td data-th="<?php esc_attr_e('Average Rating', 'book-database'); ?>">
		{{ data.average_rating }}
	</td>
</tr>
