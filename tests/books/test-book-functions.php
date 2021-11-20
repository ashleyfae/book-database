<?php
/**
 * Test: Book Functions
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use Book_Database\Models\Book;
use Book_Database\Exceptions\Exception;
use function Book_Database\add_book;
use function Book_Database\add_book_series;
use function Book_Database\get_book;
use function Book_Database\get_book_by;
use function Book_Database\get_books;

/**
 * Class Test_Book_Functions
 *
 * @package Book_Database\Tests
 */
class Test_Book_Functions extends UnitTestCase {

	/**
	 * @var int ID of the created book.
	 */
	protected static $book_id;

	/**
	 * @var Book[]
	 */
	protected static $books;

	/**
	 * Create a new book at the start of tests
	 */
	public function setUp() {
		parent::setUp();

		try {
			self::$book_id = $this->bdb()->book->create( array(
				'title'       => 'The First Book',
				'index_title' => 'First Book, The'
			) );

			self::$books = $this->bdb()->book->create_many( 5 );
		} catch ( \Exception $e ) {

		}
	}

	/**
	 * Ensure we've got a book ID returned
	 *
	 * @covers ::add_book
	 */
	public function test_add_book_returns_book_id() {
		$this->assertGreaterThan( 0, self::$book_id );
	}

	/**
	 * Creating a book without a title throws an exception
	 *
	 * @covers ::add_book
	 * @throws Exception
	 */
	public function test_create_book_without_title_throws_exception() {
		$this->setExpectedException( Exception::class, __( 'Book title is required.', 'book-database' ) );

		add_book();
	}

	/**
	 * @covers ::get_book
	 */
	public function test_get_book() {
		$this->assertInstanceOf( 'Book_Database\Models\Book', get_book( self::$book_id ) );
	}

	/**
	 * @covers ::get_book_by
	 */
	public function test_get_book_by_title() {
		$this->assertInstanceOf( 'Book_Database\Models\Book', get_book_by( 'title', 'The First Book' ) );
	}

	/**
	 * @covers ::get_book_by
	 */
	public function test_get_book_by_index_title() {
		$this->assertInstanceOf( 'Book_Database\Models\Book', get_book_by( 'index_title', 'First Book, The' ) );
	}

	/**
	 * @covers ::get_books
	 */
	public function test_get_books_array_of_book_objects() {
		$books = get_books();

		$this->assertInstanceOf( 'Book_Database\Models\Book', $books[0] );
	}

	/**
	 * Get books ordered by ID, ASC
	 *
	 * @covers ::get_books
	 */
	public function test_get_books_with_orderby_id_asc() {
		$books = get_books( array(
			'orderby' => 'id',
			'order'   => 'ASC'
		) );

		$this->assertTrue( $books[0]->get_id() < $books[1]->get_id() );
	}

	/**
	 * Ensure series ID is `null` if adding a book without a series
	 *
	 * @covers Book::get_series_id
	 */
	public function test_add_book_with_series_id_null() {
		$integer_book = $this->bdb()->book->create_and_get();

		$this->assertNull( $integer_book->get_series_id() );
	}

	/**
	 * Ensure series position is `null` if adding a book without a series
	 *
	 * @covers Book::get_series_id
	 * @covers Book::get_series_position
	 */
	public function test_add_book_with_series_position_null() {
		$integer_book = $this->bdb()->book->create_and_get();

		$this->assertNull( $integer_book->get_series_position() );
	}

	/**
	 * Ensure we can add a series position as an integer and that it returns an integer value
	 *
	 * @covers Book::get_series_position
	 */
	public function test_add_book_with_series_position_integer() {
		$series_id = $this->bdb()->series->create();

		$integer_book = $this->bdb()->book->create_and_get( array(
			'series_id'       => $series_id,
			'series_position' => 1
		) );

		$this->assertEquals( 1, $integer_book->get_series_position() );
	}

	/**
	 * Ensure we can add a series position as a float and that it returns a float value
	 *
	 * @covers Book::get_series_position
	 */
	public function test_add_book_with_series_position_float() {
		$series_id = $this->bdb()->series->create();

		$integer_book = $this->bdb()->book->create_and_get( array(
			'series_id'       => $series_id,
			'series_position' => 1.5
		) );

		$this->assertEquals( 1.5, $integer_book->get_series_position() );
	}

}
