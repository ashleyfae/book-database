<?php
/**
 * Base Unit Test Case
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use function Book_Database\book_database;

require_once dirname( __FILE__ ) . '/factory.php';

/**
 * Class UnitTestCase
 *
 * @package Book_Database
 */
class UnitTestCase extends \WP_UnitTestCase {

	/**
	 * Delete Book Database table data after each class.
	 */
	public static function tearDownAfterClass() {
		self::deleteBookDatabaseData();

		return parent::tearDownAfterClass();
	}

	/**
	 * Get the factory
	 *
	 * @return Factory|null
	 */
	protected static function bdb() {
		static $factory = null;

		if ( ! $factory ) {
			$factory = new Factory();
		}

		return $factory;
	}

	/**
	 * Truncate all Book Database tables
	 */
	protected static function deleteBookDatabaseData() {

		$tables = book_database()->get_tables();

		foreach ( $tables as $table ) {
			if ( method_exists( $table, 'truncate' ) ) {
				$table->truncate();
			}
		}

	}

}