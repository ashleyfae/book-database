<?php

/**
 * HTML Class
 *
 * Class for easily creating HTML form fields.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
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
	 * Rating Dropdown
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function rating_dropdown( $args = array() ) {

		$defaults = array(
			'options'          => bdb_get_available_ratings(),
			'id'               => 'book_rating',
			'name'             => 'book_rating',
			'show_option_all'  => false,
			'show_option_none' => false
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->select( $args );

	}

	/**
	 * Term Dropdown
	 *
	 * @param string $type Term type.
	 * @param array  $args Arguments to override the defaults.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return string|false
	 */
	public function term_dropdown( $type, $args = array() ) {

		$terms = bdb_get_terms( array(
			'number'  => 100,
			'type'    => $type,
			'orderby' => 'name',
			'order'   => 'ASC'
		) );

		if ( ! $terms ) {
			return false;
		}

		$options = array();

		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		if ( ! count( $options ) ) {
			return false;
		}

		// Set up default args.
		$defaults = array(
			'options'          => $options,
			'id'               => sanitize_html_class( $type . '_terms' ),
			'name'             => sanitize_html_class( $type ),
			'show_option_all'  => esc_html__( 'Any', 'book-database' ),
			'show_option_none' => false
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->select( $args );

	}

	/**
	 * Select Dropdown
	 *
	 * @param array $args Arguments to override the defaults.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function select( $args = array() ) {

		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => false,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'book-database' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'book-database' ),
			'data'             => array(),
			'readonly'         => false,
			'disabled'         => false,
			'desc'             => null
		);

		$args = wp_parse_args( $args, $defaults );

		$data_elements = '';
		foreach ( $args['data'] as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		if ( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if ( $args['chosen'] ) {
			$args['class'] .= ' bookdb-select-chosen';
			if ( is_rtl() ) {
				$args['class'] .= ' chosen-rtl';
			}
		}

		if ( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$readonly = ' readonly="readonly"';
		} else {
			$readonly = '';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}

		$class  = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$output = '<select' . $disabled . $readonly . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( bdb_sanitize_key( str_replace( '-', '_', $args['id'] ) ) ) . '" class="bookdb-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"' . $data_elements . '>';

		if ( $args['show_option_all'] ) {
			if ( $args['multiple'] ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {

			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( - 1, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], - 1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {

				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
				} else {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<span class="bookdb-description">' . esc_html( $args['desc'] ) . '</span>';
		}

		return $output;

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
			'desc'    => '',
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

		$output = '';

		if ( ! empty( $args['desc'] ) ) {
			$output .= '<label class="bookdb-description">';
		}

		$output .= '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $args['value'] ) . '" ' . checked( 1, $args['current'], false ) . '>';

		if ( ! empty( $args['desc'] ) ) {
			$output .= esc_html( $args['desc'] ) . '</label>';
		}

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
			$output .= '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] . '-' . sanitize_html_class( $value ) ) . '" class="' . $class . '" value="' . esc_attr( $value ) . '" ' . $checked . '>';
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