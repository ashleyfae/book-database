<?php
/**
 * Editions Template: Table Row
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

$retailers = get_retailers( array(
	'orderby' => 'name',
	'order'   => 'ASC',
	'number'  => 50
) );
?>
<div class="bdb-book-link" data-id="{{ data.id }}">
	<label for="bdb-book-link-{{ data.id }}-retailer" class="screen-reader-text"><?php _e( 'Select a retailer', 'book-database' ); ?></label>
	<select id="bdb-book-link-{{ data.id }}-retailer" class="bdb-book-link-retailer">
		<?php foreach ( $retailers as $retailer ) : ?>
			<option value="<?php echo esc_attr( $retailer->get_id() ); ?>"<# if ( data.retailer_id == '<?php echo $retailer->get_id(); ?>' ) { #> selected="selected" <# } #>><?php echo esc_html( $retailer->get_name() ); ?></option>
		<?php endforeach; ?>
	</select>

	<label for="bdb-book-link-{{ data.id }}-url" class="screen-reader-text"><?php _e( 'Enter a URL', 'book-database' ); ?></label>
	<input type="text" id="bdb-book-link-{{ data.url }}-url" class="regular-text bdb-book-link-url" placeholder="https://" value="{{ data.url }}">

	<?php if ( user_can_edit_books() ) : ?>
		<button type="button" class="button bdb-update-book-link"><?php _e( 'Update', 'book-database' ); ?></button>
		<button type="button" class="button bdb-remove-book-link"><?php _e( 'Remove', 'book-database' ); ?></button>
	<?php endif; ?>
</div>