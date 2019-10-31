<?php
/**
 * Retailers Template: Table Row
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;
?>
<tr id="bdb-retailer-{{ data.id }}" data-id="{{ data.id }}">
	<td class="bdb-retailer-name">
		<label for="bdb-retailer-name-{{ data.id }}" class="screen-reader-text"><?php _e( 'Enter a name for the retailer', 'book-database' ); ?></label>
		<input type="text" id="bdb-retailer-name-{{ data.id }}" value="{{ data.name }}">
	</td>
	<td class="bdb-retailer-actions">
		<button type="button" class="button bdb-update-retailer"><?php _e('Update', 'book-database' ); ?></button>
		<button type="button" class="button bdb-remove-retailer"><?php _e('Remove', 'book-database' ); ?></button>
	</td>
</tr>