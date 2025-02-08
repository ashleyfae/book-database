<?php
/**
 * Test: Book Object
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use Book_Database\Models\Book;
use function Book_Database\add_book;
use function Book_Database\add_book_series;
use function Book_Database\get_book;

/**
 * Class Test_Book_Object
 *
 * @package Book_Database
 */
class Test_Book_Object extends UnitTestCase {

	/**
	 * @var int ID of the created book.
	 */
	protected static $book_id;

	/**
	 * @var int ID of the series.
	 */
	protected static $series_id;

	/**
	 * @var Book Book object
	 */
	protected static $book;

	/**
	 * Create a new book at the start of tests
	 */
	public function setUp() : void {
		parent::setUp();

		try {
			self::$series_id = add_book_series( array(
				'name' => 'First Book Trilogy'
			) );

			self::$book_id = add_book( array(
				'title'           => 'The First Book',
				'index_title'     => 'First Book, The',
				'series_id'       => self::$series_id,
				'series_position' => 1,
				'pub_date'        => date( 'Y-m-d' ),
				'pages'           => 123,
			) );

			self::$book = get_book( self::$book_id );
		} catch ( \Exception $e ) {

		}
	}

	/**
	 * @covers Book::get_index_title
	 */
	public function test_index_title() {
		$this->assertEquals( 'First Book, The', self::$book->get_index_title() );
	}

	/**
	 * @covers Book::get_series_id
	 */
	public function test_series_id() {
		$this->assertEquals( self::$series_id, self::$book->get_series_id() );
	}

	/**
	 * @covers Book::get_series_id
	 */
	public function test_series_name() {
		$this->assertEquals( 'First Book Trilogy', self::$book->get_series_name() );
	}

	/**
	 * @covers Book::get_series_position
	 */
	public function test_series_position() {
		$this->assertEquals( 1, self::$book->get_series_position() );
	}

	/**
	 * @covers Book::get_pub_date
	 */
	public function test_pub_date() {
		$this->assertEquals( date( 'Y-m-d' ), self::$book->get_pub_date( false ) );
	}

	/**
	 * @covers Book::get_pages
	 */
	public function test_pages() {
		$this->assertEquals( 123, self::$book->get_pages() );
	}

}
