<?php
/**
 * Test: Series Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use Book_Database\Models\Series;
use function Book_Database\get_book_series_by;

/**
 * Class Test_Series_Functions
 *
 * @package Book_Database\Tests
 */
class Test_Series_Functions extends UnitTestCase {

	protected static $series_id;

	/**
	 * @var Series[]
	 */
	protected static $series;

	/**
	 * Create a new book at the start of tests
	 */
	public function setUp() {
		parent::setUp();

		try {
			self::$series_id = $this->bdb()->series->create( array(
				'name' => 'My Trilogy',
				'slug' => 'my-trilogy'
			) );

			self::$series = $this->bdb()->series->create_many( 5 );
		} catch ( \Exception $e ) {

		}
	}

	/**
	 * @covers ::\Book_Database\get_book_series_by()
	 */
	public function test_get_series_by_id() {
		$this->assertInstanceOf( 'Book_Database\Models\Series', get_book_series_by( 'id', self::$series[0] ) );
	}

	/**
	 * @covers ::\Book_Database\get_book_series_by()
	 */
	public function test_get_series_by_name() {
		$this->assertInstanceOf( 'Book_Database\Models\Series', get_book_series_by( 'name', 'My Trilogy' ) );
	}

	/**
	 * @covers ::\Book_Database\get_book_series_by()
	 */
	public function test_get_series_by_slug() {
		$this->assertInstanceOf( 'Book_Database\Models\Series', get_book_series_by( 'slug', 'my-trilogy' ) );
	}

	/**
	 * If you try to create two series with the same name, the second one should have a new slug
	 *
	 * @covers ::\Book_Database\add_book_series()
	 * @covers ::\Book_Database\unique_book_slug()
	 */
	public function test_series_same_slug_should_append_number() {

		$first_series_id = $this->bdb()->series->create( array(
			'name' => 'Book Series',
			'slug' => 'book-series'
		) );
		$first_series    = get_book_series_by( 'id', $first_series_id );

		$this->assertEquals( 'book-series', $first_series->get_slug() );

		$second_series_id = $this->bdb()->series->create( array(
			'name' => 'Book Series',
			'slug' => 'book-series'
		) );
		$second_series    = get_book_series_by( 'id', $second_series_id );

		$this->assertEquals( 'book-series-2', $second_series->get_slug() );

	}

}
