<?php

/**
 * HTML Class
 *
 * Class for easily creating HTML form fields.
 *
 * @package   book-database
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BDB_HTML
 */
class BDB_HTML {

	/**
	 * Select Dropdown
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function select( $args = array() ) {

	}

	/**
	 * Checkbox
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function checkbox( $args = array() ) {

		$defaults = array(
			'value'   => 1,
			'id'      => null,
			'name'    => null,
			'current' => null,
			'class'   => 'bookdb-checkbox',
			'options' => array(
				'disabled' => false,
				'readonly' => false
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$class   = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$options = '';

		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$output = '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $args['value'] ) . '" ' . checked( 1, $args['current'], false ) . '>';

		return $output;

	}

	/**
	 * Multicheck
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string
	 */
	public function multicheck( $args = array() ) {

		$defaults = array(
			'choices' => array(), // value => name pairs
			'id'      => null,
			'name'    => null,
			'current' => array(),
			'class'   => 'bookdb-checkbox',
			'options' => array(
				'disabled' => false,
				'readonly' => false
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$class   = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$options = '';
		$output  = '';

		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		foreach ( $args['choices'] as $value => $name ) {
			$checked = in_array( $value, $args['current'] ) ? ' checked="checked"' : '';

			$output .= '<label for="' . esc_attr( $args['id'] . '-' . sanitize_html_class( $value ) ) . '">';
			$output .= '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] . '-' . sanitize_html_class( $value ) ) . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $value ) . '" ' . $checked . '>';
			$output .= esc_html( $name ) . '</label>';
		}

		return $output;

	}

	/**
	 * Text Field
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function text( $args = array() ) {

		$defaults = array(
			'id'           => '',
			'name'         => null,
			'value'        => null,
			'label'        => null,
			'desc'         => null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false,
			'type'         => 'text'
		);

		$args = wp_parse_args( $args, $defaults );

		$class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . bdb_sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}

		$output = '';

		if ( ! empty( $args['label'] ) ) {
			$output .= '<label class="bookdb-label" for="' . bdb_sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
		}

		$output .= '<input type="' . esc_attr( $args['type'] ) . '" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="bookdb-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		return $output;

	}

	/**
	 * Textarea Field
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function textarea( $args = array() ) {

		$defaults = array(
			'id'           => '',
			'name'         => null,
			'value'        => null,
			'label'        => null,
			'desc'         => null,
			'placeholder'  => '',
			'class'        => 'large-textarea',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false,
			'rows'         => false,
			'columns'      => false
		);

		$args = wp_parse_args( $args, $defaults );

		$class    = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if ( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . bdb_sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}

		$rows = $cols = '';

		if ( $args['rows'] ) {
			$rows = ' rows="' . absint( $args['rows'] ) . '"';
		}
		if ( $args['columns'] ) {
			$cols = ' columns="' . absint( $args['columns'] ) . '"';
		}

		$output = '';

		if ( ! empty( $args['label'] ) ) {
			$output .= '<label class="bookdb-label" for="' . bdb_sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
		}

		$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" autocomplete="' . esc_attr( $args['autocomplete'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $rows . $cols . ' ' . $data . '' . $disabled . '>' . esc_textarea( $args['value'] ) . '</textarea>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="bookdb-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		return $output;

	}

	/**
	 * Meta Row
	 *
	 * @param string $type       Type of field
	 * @param array  $meta_args  Arguments for the meta field HTML itself.
	 * @param array  $field_args Form field arguments
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function meta_row( $type = 'text', $meta_args = array(), $field_args = array() ) {

		$defaults = array(
			'label' => '',
			'field' => ''
		);

		$args = wp_parse_args( $meta_args, $defaults );
		?>
		<div class="bookdb-box-row">
			<label for="<?php echo array_key_exists( 'id', $field_args ) ? esc_attr( $field_args['id'] ) : ''; ?>"><?php echo esc_html( $args['label'] ); ?></label>
			<div class="bookdb-input-wrapper">
				<?php
				if ( method_exists( $this, $type ) ) {
					echo $this->$type( $field_args );
				}

				echo $args['field'];
				?>
			</div>
		</div>
		<?php

	}

}