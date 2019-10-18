<?php
/**
 * Edition Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

/**
 * Class Edition
 * @package Book_Database
 */
class Edition extends Base_Object {

	protected $book_id = 0;

	protected $isbn = '';

	protected $format = '';

	protected $date_acquired = '';

	protected $source_id = 0;

	protected $signed = null;

	/**
	 * Get the ID of the book
	 *
	 * @return int
	 */
	public function get_book_id() {
		return absint( $this->book_id );
	}

	/**
	 * Get the ISBN
	 *
	 * @return string
	 */
	public function get_isbn() {
		return $this->isbn;
	}

	/**
	 * Get the format (ebook, hardback, etc.)
	 *
	 * @return string
	 */
	public function get_format() {
		return $this->format;
	}

	/**
	 * Get the date the book was acquired
	 *
	 * @param bool   $formatted Whether or not to format the date for display.
	 * @param string $format    Format to display the formatted date in. Default to site format.
	 *
	 * @return string
	 */
	public function get_date_acquired( $formatted = false, $format = '' ) {
		return ( ! empty( $this->date_acquired ) && $formatted ) ? format_date( $this->date_acquired, $format ) : $this->date_acquired;
	}

	/**
	 * Get the ID of the source term
	 *
	 * @return int
	 */
	public function get_source_id() {
		return absint( $this->source_id );
	}

	/**
	 * Whether or not the book is signed
	 *
	 * @return bool
	 */
	public function is_signed() {
		return ! empty( $this->signed );
	}

	/**
	 * Export properties
	 *
	 * @return array
	 */
	public function export_vars() {
		$vars                = parent::export_vars();
		$vars['format_name'] = get_book_formats()[ $this->get_format() ] ?? '';

		if ( $this->get_source_id() ) {
			$source = get_book_term( $this->get_source_id() );

			if ( $source instanceof Book_Term ) {
				$vars['source_name'] = $source->get_name();
			}
		}

		return $vars;
	}

}