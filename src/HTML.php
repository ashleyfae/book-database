<?php
/**
 * HTML Helper Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class HTML
 * @package Book_Database
 */
class HTML {

	/**
	 * Output a meta row
	 *
	 * @param array $args
	 */
	public function meta_row( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'label' => '',
			'field' => '',
			'id'    => ''
		) );
		?>
		<div class="bdb-meta-row">
			<label for="<?php echo esc_attr( $args['id'] ) ?? ''; ?>"><?php echo esc_html( $args['label'] ); ?></label>
			<div class="bdb-meta-value">
				<?php echo $args['field']; ?>
			</div>
		</div>
		<?php

	}

	/**
	 * Render a taxonomy field according to its setting (checkboxes or text input)
	 *
	 * @param Book_Taxonomy|string $taxonomy Taxonomy object or slug.
	 * @param array                $args     Display arguments.
	 */
	public function taxonomy_field( $taxonomy, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'selected' => array(),
			'number'   => 300,
			'id'       => ''
		) );

		if ( ! $taxonomy instanceof Book_Taxonomy ) {
			$taxonomy = get_book_taxonomy_by( 'slug', $taxonomy );
		}

		if ( ! $taxonomy instanceof Book_Taxonomy ) {
			return;
		}

		if ( 'checkbox' === $taxonomy->get_format() ) {

			// "Categories"

			// Get all terms EXCEPT the ones already checked.
			$all_terms = get_book_terms( array(
				'number'       => 300,
				'taxonomy'     => $taxonomy->get_slug(),
				'name__not_in' => $args['selected'],
				'fields'       => 'name',
				'orderby'      => 'name',
				'order'        => 'ASC'
			) );

			$final_terms = $args['selected'] + $all_terms;
			?>
			<div id="bdb-checkboxes-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() . '-' . $args['id'] ) ); ?>" class="bdb-taxonomy-checkboxes" data-taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>" data-name="<?php echo esc_attr( 'book_terms[' . $taxonomy->get_slug() . '][]' ); ?>">
				<div class="bdb-checkbox-wrap">
					<?php
					foreach ( $final_terms as $term_name ) {
						?>
						<label for="<?php echo esc_attr( sanitize_html_class( sanitize_key( sprintf( '%s-%s-%s', $taxonomy->get_slug(), $term_name, $args['id'] ) ) ) ); ?>">
							<input type="checkbox" id="<?php echo esc_attr( sanitize_html_class( sanitize_key( sprintf( '%s-%s-%s', $taxonomy->get_slug(), $term_name, $args['id'] ) ) ) ); ?>" class="bdb-checkbox" name="book_terms[<?php echo esc_attr( $taxonomy->get_slug() ); ?>][]" value="<?php echo esc_attr( $term_name ); ?>" <?php checked( in_array( $term_name, $args['selected'] ) ); ?>>
							<?php echo esc_html( $term_name ); ?>
						</label>
						<?php
					}
					?>
				</div>
				<div class="bdb-new-checkbox-term">
					<label for="bdb-new-checkbox-term-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="screen-reader-text"><?php printf( esc_html__( 'Enter the name of a new %s', 'book-database' ), esc_html( lcfirst( $taxonomy->get_name() ) ) ); ?></label>
					<input type="text" id="bdb-new-checkbox-term-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="regular-text bdb-new-checkbox-term-value">
					<input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>">
				</div>
			</div>
			<?php

		} else {

			// "Tags"

			?>
			<div id="bdb-tags-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" class="bdb-tags-wrap" data-taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>">
				<div class="jaxtag">
					<div class="nojs-tags hide-if-js">
						<label for="bdb-input-tag-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>"><?php printf( __( 'Enter the name of the %s', 'book-database' ), esc_html( $taxonomy->get_name() ) ); ?></label>
						<textarea name="book_terms[<?php echo esc_attr( $taxonomy->get_slug() ); ?>]" rows="3" cols="20" id="bdb-input-tag-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy->get_slug() ); ?>"><?php echo esc_textarea( implode( ', ', $args['selected'] ) ); ?></textarea>
					</div>
					<div class="bdb-ajaxtag hide-if-no-js">
						<p>
							<label for="bdb-new-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>-term" class="screen-reader-text"><?php printf( __( 'Enter the name of the %s', 'book-database' ), esc_html( $taxonomy->get_name() ) ); ?></label>
							<input type="text" id="bdb-new-<?php echo esc_attr( sanitize_html_class( $taxonomy->get_slug() ) ); ?>-term" class="form-input-tip regular-text bdb-new-tag" size="16" autocomplete="off" value="">
							<input type="button" class="button" value="<?php esc_attr_e( 'Add', 'book-database' ); ?>" tabindex="3">
						</p>
					</div>
				</div>
				<div class="bdb-tags-checklist"></div>
			</div>
			<?php

		}

	}

}
