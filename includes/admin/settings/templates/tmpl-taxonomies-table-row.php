<?php
/**
 * Taxonomies Template: Table Row
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;
?>
<tr id="bdb-book-taxonomy-{{ data.id }}" data-id="{{ data.id }}">
	<td class="bdb-book-taxonomy-name column-primary" data-colname="<?php esc_attr_e( 'Name', 'book-database' ); ?>">
		<label for="bdb-book-taxonomy-name-{{ data.id }}" class="screen-reader-text"><?php _e( 'Enter a name for the taxonomy', 'book-database' ); ?></label>
		<input type="text" id="bdb-book-taxonomy-name-{{ data.id }}" value="{{ data.name }}"<# if ( data.slug == 'author' ) { #> readonly="readonly" <# } #>>

		<button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span></button>
	</td>
	<td class="bdb-book-taxonomy-slug" data-colname="<?php esc_attr_e( 'Slug', 'book-database' ); ?>">
		<label for="bdb-book-taxonomy-slug-{{ data.id }}" class="screen-reader-text"><?php _e( 'Enter a unique slug for the taxonomy', 'book-database' ); ?></label>
		<input type="text" id="bdb-book-taxonomy-slug-{{ data.id }}" value="{{ data.slug }}" readonly="readonly">
	</td>
	<td class="bdb-book-taxonomy-format" data-colname="<?php esc_attr_e( 'Format', 'book-database' ); ?>">
		<label for="bdb-book-taxonomy-format-{{ data.id }}" class="screen-reader-text"><?php _e( 'Select a format for the taxonomy terms', 'book-database' ); ?></label>
		<select id="bdb-book-taxonomy-format-{{ data.id }}">
			<option value="text"<# if ( data.format == 'text' ) { #> selected="selected" <# } #>><?php _e( 'Text', 'book-database' ); ?></option>
			<option value="checkbox"<# if ( data.format == 'checkbox' ) { #> selected="selected" <# } #>><?php _e( 'Checkbox', 'book-database' ); ?></option>
		</select>
	</td>
	<td class="bdb-book-taxonomy-actions" data-colname="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
		<button type="button" class="button bdb-update-book-taxonomy"><?php _e('Update', 'book-database' ); ?></button>
		<# if ( data.slug != 'publisher' && data.slug != 'genre' && data.slug != 'source' ) { #><button type="button" class="button bdb-remove-book-taxonomy"><?php _e('Remove', 'book-database' ); ?></button><# } #>
	</td>
</tr>