<?php
/**
 * Book Factory
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests\Factory;

use Book_Database\Book;
use Book_Database\Exception;
use function Book_Database\add_book;
use function Book_Database\get_book;
use function Book_Database\update_book;

/**
 * Class Book_Factory
 *
 * @package Book_Database\Tests\Factory
 */
class Book_Factory extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * Book_Factory constructor.
	 *
	 * @param       $factory
	 * @param array $default_generation_definitions
	 */
	public function __construct( $factory, $default_generation_definitions = array() ) {
		parent::__construct( $factory, $default_generation_definitions );

		$this->default_generation_definitions = array(
			'title'       => new \WP_UnitTest_Generator_Sequence( 'Book Title %s' ),
			'index_title' => new \WP_UnitTest_Generator_Sequence( 'Book Title %s' ),
			'pub_date'    => date( 'Y-m-d' ),
			'pages'       => 100
		);
	}

	/**
	 * Create and get
	 *
	 * Used for autocomplete in IDEs.
	 *
	 * @param array      $args
	 * @param null|array $generation_definitions
	 *
	 * @return Book|false
	 */
	public function create_and_get( $args = array(), $generation_definitions = null ) {
		return parent::create_and_get( $args, $generation_definitions );
	}

	/**
	 * Create
	 *
	 * @param array $args
	 *
	 * @return int ID of the created object.
	 * @throws Exception
	 */
	public function create_object( $args ) {
		return add_book( $args );
	}

	/**
	 * Update
	 *
	 * @param int   $object_id
	 * @param array $args
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function update_object( $object_id, $args ) {
		return update_book( $object_id, $args );
	}

	/**
	 * Get a single object by ID
	 *
	 * @param int $object_id
	 *
	 * @return Book|false
	 */
	public function get_object_by_id( $object_id ) {
		return get_book( $object_id );
	}

}