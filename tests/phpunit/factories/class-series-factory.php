<?php
/**
 * Series Factory
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests\Factory;

use Book_Database\Exception;
use Book_Database\Series;
use function Book_Database\add_book_series;
use function Book_Database\get_book_series_by;
use function Book_Database\update_book_series;

/**
 * Class Series_Factory
 *
 * @package Book_Database\Tests\Factory
 */
class Series_Factory extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * Series_Factory constructor.
	 *
	 * @param       $factory
	 * @param array $default_generation_definitions
	 */
	public function __construct( $factory, $default_generation_definitions = array() ) {
		parent::__construct( $factory, $default_generation_definitions );

		$this->default_generation_definitions = array(
			'name'        => new \WP_UnitTest_Generator_Sequence( 'Series Name %s' ),
			'description' => new \WP_UnitTest_Generator_Sequence( 'Series Description %s' )
		);
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
		return add_book_series( $args );
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
		return update_book_series( $object_id, $args );
	}

	/**
	 * Get a single object by ID
	 *
	 * @param int $object_id
	 *
	 * @return Series|false
	 */
	public function get_object_by_id( $object_id ) {
		return get_book_series_by( 'id', $object_id );
	}

}