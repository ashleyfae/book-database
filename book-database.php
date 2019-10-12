<?php
/**
 * Plugin Name: Book Database
 * Plugin URI: https://github.com/nosegraze/book-database
 * Description: Maintain a database of books and reviews.
 * Version: 1.0
 * Author: Ashley Gibson
 * Author URI: http://www.nosegraze.com
 * License: GPL2 License
 * URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: book-database
 * Domain Path: /languages
 *
 * Book Database is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Book Database is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Book Database. If not, see <http://www.gnu.org/licenses/>.
 *
 * Thanks to Easy Digital Downloads for serving as a great code base
 * and resource, which a lot of Book Database's structure is based on.
 * Easy Digital Downloads is made by Pippin Williamson and licensed
 * under GPL2+.
 *
 * @package   book-database
 * @copyright Copyright (c) 2017, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BDB_VERSION' ) ) {
	define( 'BDB_VERSION', '1.0' );
}
if ( ! defined( 'BDB_DIR' ) ) {
	define( 'BDB_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'BDB_URL' ) ) {
	define( 'BDB_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'BDB_FILE' ) ) {
	define( 'BDB_FILE', __FILE__ );
}
if ( ! defined( 'NOSE_GRAZE_STORE_URL' ) ) {
	define( 'NOSE_GRAZE_STORE_URL', 'https://shop.nosegraze.com' );
}

final class Book_Database {

	/**
	 * Instance of the Book_Database class.
	 *
	 * @var Book_Database
	 */
	private static $instance;

	/**
	 * Array of custom table objects
	 *
	 * @var array
	 */
	private $tables = array();

	/**
	 * @var REST_API
	 */
	private $rest_api;

	/**
	 * @var HTML
	 */
	private $html;

	/**
	 * Book_Database instance.
	 *
	 * @return Book_Database Instance of Book_Database class
	 */
	public static function instance() {

		// Return if already instantiated
		if ( self::is_instantiated() ) {
			return self::$instance;
		}

		// Set up the singleton.
		self::setup_instance();

		// Bootstrap
		self::$instance->setup_files();
		self::$instance->setup_application();

		register_activation_hook( __FILE__, array( self::$instance, 'install' ) );

		return self::$instance;

	}

	/**
	 * Whether the main class has been instantiated or not.
	 *
	 * @return bool
	 */
	private static function is_instantiated() {

		// Return true if instance is correct class
		if ( ! empty( self::$instance ) && ( self::$instance instanceof Book_Database ) ) {
			return true;
		}

		// Return false if not instantiated correctly
		return false;

	}

	/**
	 * Set up the singleton instance
	 */
	private static function setup_instance() {
		self::$instance = new Book_Database();
	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function setup_files() {
		$this->include_files();

		// Admin
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->include_admin();
		} else {
			$this->include_frontend();
		}
	}

	/**
	 * Include global files
	 */
	private function include_files() {

		require_once BDB_DIR . 'includes/abstract-class-base-object.php';
		require_once BDB_DIR . 'includes/class-exception.php';

		// Database engine
		require_once BDB_DIR . 'includes/database/engine/base.php';
		require_once BDB_DIR . 'includes/database/engine/table.php';
		require_once BDB_DIR . 'includes/database/engine/query.php';
		require_once BDB_DIR . 'includes/database/engine/column.php';
		require_once BDB_DIR . 'includes/database/engine/row.php';
		require_once BDB_DIR . 'includes/database/engine/schema.php';
		require_once BDB_DIR . 'includes/database/engine/compare.php';
		require_once BDB_DIR . 'includes/database/engine/date.php';
		require_once BDB_DIR . 'includes/database/engine/tax.php';

		// Database - books
		require_once BDB_DIR . 'includes/database/books/class-books-table.php';
		require_once BDB_DIR . 'includes/database/books/class-books-schema.php';
		require_once BDB_DIR . 'includes/database/books/class-books-query.php';

		// Database - book_taxonomies
		require_once BDB_DIR . 'includes/database/book-taxonomies/class-book-taxonomies-table.php';
		require_once BDB_DIR . 'includes/database/book-taxonomies/class-book-taxonomies-schema.php';
		require_once BDB_DIR . 'includes/database/book-taxonomies/class-book-taxonomies-query.php';

		// Database - book_term_relationships
		require_once BDB_DIR . 'includes/database/book-term-relationships/class-book-term-relationships-table.php';
		require_once BDB_DIR . 'includes/database/book-term-relationships/class-book-term-relationships-schema.php';
		require_once BDB_DIR . 'includes/database/book-term-relationships/class-book-term-relationships-query.php';

		// Database - book_terms
		require_once BDB_DIR . 'includes/database/book-terms/class-book-terms-table.php';
		require_once BDB_DIR . 'includes/database/book-terms/class-book-terms-schema.php';
		require_once BDB_DIR . 'includes/database/book-terms/class-book-terms-query.php';

		// Database - series
		require_once BDB_DIR . 'includes/database/series/class-series-table.php';
		require_once BDB_DIR . 'includes/database/series/class-series-schema.php';
		require_once BDB_DIR . 'includes/database/series/class-series-query.php';

		// Books
		require_once BDB_DIR . 'includes/books/class-book.php';
		require_once BDB_DIR . 'includes/books/book-functions.php';
		require_once BDB_DIR . 'includes/books/book-layout-functions.php';

		// Book Taxonomies
		require_once BDB_DIR . 'includes/book-taxonomies/class-book-taxonomy.php';
		require_once BDB_DIR . 'includes/book-taxonomies/book-taxonomy-functions.php';

		// Book Term Relationships
		require_once BDB_DIR . 'includes/book-term-relationships/class-book-term-relationship.php';
		require_once BDB_DIR . 'includes/book-term-relationships/book-term-relationship-actions.php';
		require_once BDB_DIR . 'includes/book-term-relationships/book-term-relationship-functions.php';

		// Book Terms
		require_once BDB_DIR . 'includes/book-terms/class-book-term.php';
		require_once BDB_DIR . 'includes/book-terms/book-term-functions.php';

		// Series
		require_once BDB_DIR . 'includes/series/class-series.php';
		require_once BDB_DIR . 'includes/series/series-functions.php';

		// REST API
		require_once BDB_DIR . 'includes/rest-api/class-rest-api.php';
		require_once BDB_DIR . 'includes/rest-api/abstract-class-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-book-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-book-term-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-taxonomy-controller.php';

		// Misc.
		require_once BDB_DIR . 'includes/class-html.php';
		require_once BDB_DIR . 'includes/misc-functions.php';

	}

	/**
	 * Include admin files
	 */
	private function include_admin() {

		require_once BDB_DIR . 'includes/admin/abstract-class-list-table.php';
		require_once BDB_DIR . 'includes/admin/admin-assets.php';
		require_once BDB_DIR . 'includes/admin/admin-notices.php';
		require_once BDB_DIR . 'includes/admin/admin-pages.php';

		// Books
		require_once BDB_DIR . 'includes/admin/books/book-actions.php';
		require_once BDB_DIR . 'includes/admin/books/book-functions.php';
		require_once BDB_DIR . 'includes/admin/books/books-page.php';
		require_once BDB_DIR . 'includes/admin/books/edit-book-fields.php';

		// Settings
		require_once BDB_DIR . 'includes/admin/settings/book-layout-functions.php';
		require_once BDB_DIR . 'includes/admin/settings/register-settings.php';
		require_once BDB_DIR . 'includes/admin/settings/display-settings.php';

	}

	/**
	 * Include front-end files
	 */
	private function include_frontend() {

	}

	/**
	 * Set up custom database tables
	 */
	private function setup_application() {

		self::$instance->tables = array(
			'book_taxonomies'         => new Book_Taxonomies_Table(),
			'book_term_relationships' => new Book_Term_Relationships_Table(),
			'book_terms'              => new Book_Terms_Table(),
			'books'                   => new Books_Table(),
			'series'                  => new Series_Table(),
		);

		self::$instance->rest_api = new REST_API();
		self::$instance->html     = new HTML();

	}

	/**
	 * Get a table object by its key
	 *
	 * @param string $table_key Table key.  One of:
	 *                          'book_taxonomies',
	 *                          'book_term_relationships',
	 *                          'book_terms'
	 *                          'books'
	 *                          'owned_editions'
	 *                          'reading_log'
	 *                          'reviewmeta'
	 *                          'reviews',
	 *                          'series'
	 *
	 * @return BerlinDB\Database\Table|false
	 */
	public function get_table( $table_key ) {
		return array_key_exists( $table_key, self::$instance->tables ) ? self::$instance->tables[ $table_key ] : false;
	}

	/**
	 * Get the HTML helper class
	 *
	 * @return HTML
	 */
	public function get_html() {
		return $this->html;
	}

	/**
	 * Run installation
	 *
	 *      - Install default taxonomies.
	 */
	public function install() {

		$default_taxonomies = array(
			'author' => array(
				'slug'   => 'author',
				'name'   => esc_html__( 'Author', 'book-database' ),
				'format' => 'text' // text, checkbox
			),
			'publisher' => array(
				'slug'   => 'publisher',
				'name'   => esc_html__( 'Publisher', 'book-database' ),
				'format' => 'text' // text, checkbox
			),
			'genre'     => array(
				'slug'   => 'genre',
				'name'   => esc_html__( 'Genre', 'book-database' ),
				'format' => 'text'
			),
			'source'    => array(
				'slug'   => 'source',
				'name'   => esc_html__( 'Source', 'book-database' ),
				'format' => 'checkbox'
			)
		);

		foreach ( $default_taxonomies as $taxonomy ) {
			try {
				add_book_taxonomy( $taxonomy );
			} catch ( Exception $e ) {

			}
		}

	}

}


/**
 * Require PHP 5.6+
 */
/**
 * Insufficient PHP version notice.
 *
 * @return void
 */
function insufficient_php_version() {
	?>
	<div class="notice notice-error">
		<p><?php printf( __( 'Book Database requires PHP version 7.0 or greater. You have version %s. Please contact your web host to upgrade your version of PHP.', 'book-database' ), PHP_VERSION ); ?></p>
	</div>
	<?php
}

/**
 * Returns the main instance of Book_Database.
 *
 * @return Book_Database|void
 */
function book_database() {
	if ( version_compare( PHP_VERSION, '7.0', '>=' ) ) {
		return Book_Database::instance();
	} else {
		add_action( 'admin_notices', __NAMESPACE__ . '\insufficient_php_version' );
	}
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\book_database', 4 );