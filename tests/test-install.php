<?php
/**
 * Test: Install
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use function Book_Database\book_database;

/**
 * Class Test_Install
 *
 * @package Book_Database
 */
class Test_Install extends UnitTestCase {

	/**
	 * @covers \Book_Database\BerlinDB\Database\install()
	 */
	public function test_tables_exist() {
		foreach ( book_database()->get_tables() as $table ) {
			$this->assertTrue( $table->exists() );
		}
	}

	/**
	 * @covers book_database::install
	 */
	public function test_administrator_has_view_books_cap() {

		$role = get_role( 'administrator' );

		$this->assertTrue( $role->has_cap( 'bdb_view_books' ) );

	}

	/**
	 * @covers book_database::install
	 */
	public function test_administrator_has_edit_books_cap() {

		$role = get_role( 'administrator' );

		$this->assertTrue( $role->has_cap( 'bdb_edit_books' ) );

	}

	/**
	 * @covers book_database::install
	 */
	public function test_administrator_has_manage_book_settings_cap() {

		$role = get_role( 'administrator' );

		$this->assertTrue( $role->has_cap( 'bdb_manage_book_settings' ) );

	}

}