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
	<td class="bdb-retailer-name column-primary" data-colname="<?php esc_attr_e( 'Name', 'book-database' ); ?>">
		<label for="bdb-retailer-name-{{ data.id }}" class="screen-reader-text"><?php _e( 'Enter a name for the retailer', 'book-database' ); ?></label>
		<input type="text" id="bdb-retailer-name-{{ data.id }}" value="{{ data.name }}">
	</td>
	<td class="bdb-retailer-template" data-colname="<?php esc_attr_e( 'Book Info Template', 'book-database' ); ?>">
		<label for="bdb-retailer-template-{{ data.id }}" class="screen-reader-text"><?php _e( 'Format the template for use in displaying book information', 'book-database' ); ?></label>
		<textarea id="bdb-retailer-template-{{ data.id }}" class="regular-text">{{ data.template }}</textarea>
	</td>
	<td class="bdb-retailer-actions" data-colname="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
		<button type="button" class="button bdb-update-retailer"><?php _e('Update', 'book-database' ); ?></button>
		<button type="button" class="button bdb-remove-retailer"><?php _e('Remove', 'book-database' ); ?></button>
	</td>
</tr>