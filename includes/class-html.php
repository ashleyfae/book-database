<?php
/**
 * HTML Helper Class
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
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

}