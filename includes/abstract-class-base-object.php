<?php
/**
 * Base Object
 *
 * Extended by core objects.
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Base_Object
 * @package Book_Database
 */
abstract class Base_Object {

	protected $id = 0;

	protected $date_created = '';

	protected $date_modified = '';

	/**
	 * Object constructor
	 *
	 * @param array|object $args Object to populate vars for.
	 */
	public function __construct( $args = array() ) {
		$this->set_vars( $args );
	}

	/**
	 * Set class properties from arguments
	 *
	 * @param array $args
	 */
	protected function set_vars( $args = array() ) {

		// Bail if empty.
		if ( empty( $args ) ) {
			return;
		}

		// Cast to an array.
		if ( ! is_array( $args ) ) {
			$args = (array) $args;
		}

		foreach ( $args as $key => $value ) {
			if ( '0000-00-00 00:00:00' === $value ) {
				$value = null;
			}

			$this->{$key} = maybe_unserialize( $value );
		}

	}

	/**
	 * Get the ID
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->id );
	}

	/**
	 * Get the created date
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_date_created( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_created ) && $formatted ) ? format_date( $this->date_created, $format ) : $this->date_created;
	}

	/**
	 * Get the modified date
	 *
	 * @param bool   $formatted Whether or not to format the result for display.
	 * @param string $format    Format to display in. Defaults to site format.
	 *
	 * @return string
	 */
	public function get_date_modified( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_modified ) && $formatted ) ? format_date( $this->date_modified, $format ) : $this->date_modified;
	}

}