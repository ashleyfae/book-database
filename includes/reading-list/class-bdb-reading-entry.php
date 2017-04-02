<?php

/**
 * Reading Entry
 *
 * Class for handling reading entries.
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
 * Class BDB_Reading_Entry
 *
 * @since 1.1.0
 */
class BDB_Reading_Entry {

	/**
	 * ID of the entry.
	 *
	 * @var int
	 * @access public
	 * @since  1.1.0
	 */
	public $ID;

	private $book_id;

	private $review_id;

	private $user_id;

	private $date_started;

	private $date_finished;

	/**
	 * BDB_Reading_Entry constructor.
	 *
	 * @param int|object|array $entry_id_or_object Entry ID to fetch from database or a prepared object in database
	 *                                             format.
	 *
	 * @access public
	 * @since  1.1.0
	 * @return bool
	 */
	public function __construct( $entry_id_or_object ) {

		$entry = ( is_object( $entry_id_or_object ) || is_array( $entry_id_or_object ) ) ? $entry_id_or_object : book_database()->reading_list->get_entry( $entry_id_or_object );

		if ( empty( $entry ) || ( ! is_object( $entry ) && ! is_array( $entry ) ) ) {
			return false;
		}

		return $this->setup_entry( $entry );

	}

	/**
	 * Setup Entry
	 *
	 * @param object|array $entry Entry from the database.
	 *
	 * @access private
	 * @since  1.1.0
	 * @return bool Whether or not the setup was successful.
	 */
	private function setup_entry( $entry ) {

		if ( ! is_object( $entry ) && ! is_array( $entry ) ) {
			return false;
		}

		foreach ( $entry as $key => $value ) {

			$this->$key = $value;

		}

		// We absolutely need an ID. Otherwise nothing works.
		if ( ! empty( $this->ID ) && ! empty( $this->book_id ) ) {
			return true;
		}

		return false;

	}

}