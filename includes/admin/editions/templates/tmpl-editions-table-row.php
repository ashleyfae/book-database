<?php
/**
 * Editions Template: Table Row
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$sources = get_book_terms( array(
	'taxonomy' => 'source',
	'orderby'  => 'name',
	'order'    => 'ASC',
	'number'   => 999
) );
?>
<tr id="bdb-edition-{{ data.id }}" data-id="{{ data.id }}">
	<td class="bdb-edition-isbn" data-th="<?php esc_attr_e( 'ISBN', 'book-database' ); ?>">
		<div class="bdb-table-display-value">{{ data.isbn }}</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-edition-isbn-{{ data.id }}" class="screen-reader-text"><?php _e( 'ISBN or ASIN', 'book-database' ); ?></label>
			<input type="text" id="bdb-edition-isbn-{{ data.id }}" value="{{ data.isbn }}">
		</div>

		<button type="button" class="toggle-row">
			<span class="screen-reader-text"><?php _e( 'Show more details', 'book-database' ); ?></span>
		</button>
	</td>

	<td class="bdb-edition-format" data-th="<?php esc_attr_e( 'Format', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.format_name ) { #>
			{{ data.format_name }}
			<# } else { #>
			&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-edition-format-{{ data.id }}" class="screen-reader-text"><?php _e( 'Format', 'book-database' ); ?></label>
			<select id="bdb-edition-format-{{ data.id }}">
				<?php foreach ( get_book_formats() as $format_key => $format_value ) : ?>
					<option value="<?php echo esc_attr( $format_key ); ?>"<# if ( data.format == '<?php echo $format_key; ?>' ) { #> selected="selected" <# } #>><?php echo esc_html( $format_value ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</td>
	
	<td class="bdb-edition-date-acquired" data-th="<?php esc_attr_e( 'Date Acquired', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.date_acquired_formatted ) { #>
				{{ data.date_acquired_formatted }}
			<# } else { #>
				&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-edition-date-acquired-{{ data.id }}" class="screen-reader-text"><?php _e( 'Date you acquired this edition', 'book-database' ); ?></label>
			<input type="text" id="bdb-edition-date-acquired-{{ data.id }}" class="bdb-datepicker" value="{{ data.date_acquired }}">
		</div>
	</td>

	<td class="bdb-edition-source" data-th="<?php esc_attr_e( 'Source', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.source_name ) { #>
			{{ data.source_name }}
			<# } else { #>
			&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<label for="bdb-edition-source-{{ data.id }}" class="screen-reader-text"><?php _e( 'Source', 'book-database' ); ?></label>
			<select id="bdb-edition-source-{{ data.id }}">
				<option value="">&ndash;</option>
				<?php foreach ( $sources as $source ) : ?>
					<option value="<?php echo esc_attr( $source->get_id() ); ?>"<# if ( data.source_id == '<?php echo esc_attr( $source->get_id() ); ?>' ) { #> selected="selected" <# } #>><?php echo esc_html( $source->get_name() ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</td>

	<td class="bdb-edition-signed" data-th="<?php esc_attr_e( 'Signed', 'book-database' ); ?>">
		<div class="bdb-table-display-value">
			<# if ( data.signed ) { #>
				<?php _e( 'Yes', 'book-database' ); ?>
			<# } else { #>
				&ndash;
			<# } #>
		</div>

		<div class="bdb-table-edit-value">
			<input type="checkbox" id="bdb-edition-signed-{{ data.id }}" value="1" <# if ( data.signed ) { #> checked="checked" <# } #>>
			<label for="bdb-edition-signed-{{ data.id }}"><?php _e( 'Yes', 'book-database' ); ?></label>
		</div>
	</td>

	<td class="bdb-edition-actions" data-th="<?php esc_attr_e( 'Actions', 'book-database' ); ?>">
		<?php if ( user_can_edit_books() ) : ?>
			<button type="button" class="button bdb-edition-toggle-editable"><?php _e( 'Edit', 'book-database' ); ?></button>
			<button type="button" class="button bdb-remove-edition"><?php _e( 'Remove', 'book-database' ); ?></button>
		<?php endif; ?>
	</td>
</tr>