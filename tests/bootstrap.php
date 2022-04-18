<?php
/**
 * PHPUnit Bootstrap
 *
 * @package   book-database
 * @copyright Copyright (c) 2019, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database\Tests;

use function Book_Database\book_database;

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME']     = '';

define( 'BDB_DOING_TESTS', true );

require_once dirname( dirname( __FILE__ ) ) . '/vendor/autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load ProvisionPress
 */
function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../book-database.php';
}

tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

/**
 * Activate Book Database
 */
activate_plugin( 'book-database/book-database.php' );

/**
 * Maybe add database tables
 */
foreach ( book_database()->get_tables() as $table ) {
	if ( ! $table->exists() ) {
		$table->install();
	} else {
		$table->truncate();
	}
}

/**
 * Run installation
 */
update_option( 'bdb_run_activation', 1 );
book_database()->install();

require_once 'phpunit/class-unittestcase.php';
