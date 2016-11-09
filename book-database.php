<?php
/**
 * Plugin Name: Book Database
 * Plugin URI: @todo
 * Description: Maintain a database of books and reviews.
 * Version: 0.1.0
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
 * @copyright Copyright (c) 2016, Ashley Gibson
 * @license   GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Book_Database' ) ) :

	class Book_Database {

		/**
		 * Book_Database object.
		 *
		 * @var Book_Database Instance of the Book_Database class.
		 * @access private
		 * @since  1.0.0
		 */
		private static $instance;

		/**
		 * BDB_DB_Reviews object
		 *
		 * @var BDB_DB_Reviews
		 * @access public
		 * @since  1.0.0
		 */
		public $reviews;

		/**
		 * @var BDB_DB_Review_Meta
		 * @access public
		 * @since  1.0.0
		 */
		public $review_meta;

		/**
		 * @var BDB_DB_Books
		 * @access public
		 * @since  1.0.0
		 */
		public $books;

		public $book_meta;

		/**
		 * @var BDB_DB_Series
		 * @access public
		 * @since  1.0.0
		 */
		public $series;

		/**
		 * @var BDB_DB_Book_Terms
		 * @access public
		 * @since  1.0.0
		 */
		public $book_terms;

		/**
		 * @var BDB_DB_Book_Term_Relationships
		 * @access public
		 * @since  1.0.0
		 */
		public $book_term_relationships;

		/**
		 * @var BDB_HTML
		 * @access public
		 * @since  1.0.0
		 */
		public $html;

		/**
		 * Book_Database instance.
		 *
		 * Insures that only one instance of Book_Database exists at any one time.
		 *
		 * @uses   Book_Database::setup_constants() Set up the plugin constants.
		 * @uses   Book_Database::includes() Include any required files.
		 * @uses   Book_Database::load_textdomain() Load the language files.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return Book_Database Instance of Book_Database class
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! self::$instance instanceof Book_Database ) {
				self::$instance = new Book_Database;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();

				self::$instance->reviews                 = new BDB_DB_Reviews();
				self::$instance->review_meta             = new BDB_DB_Review_Meta();
				self::$instance->books                   = new BDB_DB_Books();
				self::$instance->series                  = new BDB_DB_Series();
				self::$instance->book_terms              = new BDB_DB_Book_Terms();
				self::$instance->book_term_relationships = new BDB_DB_Book_Term_Relationships();
				self::$instance->html                    = new BDB_HTML();
			}

			return self::$instance;

		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @access protected
		 * @since  1.0.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'book-database' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access protected
		 * @since  1.0.0
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'book-database' ), '1.0.0' );
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since  1.0.0
		 * @return void
		 */
		private function setup_constants() {

			if ( ! defined( 'BDB_VERSION' ) ) {
				define( 'BDB_VERSION', '3.3.0' );
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

		}

		/**
		 * Include Required Files
		 *
		 * @access private
		 * @since  1.0.0
		 * @return void
		 */
		private function includes() {

			global $bdb_options;

			// Settings.
			require_once BDB_DIR . 'includes/admin/settings/register-settings.php';
			if ( empty( $bdb_options ) ) {
				$bdb_options = bdb_get_settings();
			}

			require_once BDB_DIR . 'includes/database/class-bdb-db.php';
			require_once BDB_DIR . 'includes/database/class-bdb-db-books.php';
			require_once BDB_DIR . 'includes/database/class-bdb-db-book-terms.php';
			require_once BDB_DIR . 'includes/database/class-bdb-db-book-term-relationships.php';
			require_once BDB_DIR . 'includes/database/class-bdb-db-reviews.php';
			require_once BDB_DIR . 'includes/database/class-bdb-db-review-meta.php';
			require_once BDB_DIR . 'includes/database/class-bdb-db-series.php';
			require_once BDB_DIR . 'includes/book-functions.php';
			require_once BDB_DIR . 'includes/book-layout.php';
			require_once BDB_DIR . 'includes/class-bdb-book.php';
			require_once BDB_DIR . 'includes/class-bdb-html.php';
			require_once BDB_DIR . 'includes/class-bdb-review.php';
			require_once BDB_DIR . 'includes/error-tracking.php';
			require_once BDB_DIR . 'includes/misc-functions.php';
			require_once BDB_DIR . 'includes/rating-functions.php';
			require_once BDB_DIR . 'includes/review-functions.php';
			require_once BDB_DIR . 'includes/term-functions.php';

			if ( is_admin() ) {
				require_once BDB_DIR . 'includes/admin/admin-actions.php';
				require_once BDB_DIR . 'includes/admin/admin-pages.php';
				require_once BDB_DIR . 'includes/admin/admin-assets.php';
				require_once BDB_DIR . 'includes/admin/books/book-actions.php';
				require_once BDB_DIR . 'includes/admin/books/book-functions.php';
				require_once BDB_DIR . 'includes/admin/books/books.php';
				require_once BDB_DIR . 'includes/admin/modal/modal.php';
				require_once BDB_DIR . 'includes/admin/modal/modal-ajax.php';
				require_once BDB_DIR . 'includes/admin/modal/modal-button.php';
				require_once BDB_DIR . 'includes/admin/modal/shortcode-preview.php';
				require_once BDB_DIR . 'includes/admin/posts/meta-box.php';
				require_once BDB_DIR . 'includes/admin/reviews/add-review.php';
				require_once BDB_DIR . 'includes/admin/reviews/reviews.php';
				require_once BDB_DIR . 'includes/admin/settings/display-settings.php';
			}

			require_once BDB_DIR . 'includes/install.php';

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			$lang_dir = dirname( plugin_basename( BDB_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'book-database/languages-directory', $lang_dir );
			load_plugin_textdomain( 'book-database', false, $lang_dir );

		}

	}

endif;


/**
 * Require PHP 5.3
 */
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	if ( is_admin() ) {
		/**
		 * Insufficient PHP version notice.
		 *
		 * @since 3.2.8
		 * @return void
		 */
		function bdb_insufficient_php_version() {
			?>
			<div class="notice notice-error">
				<p><?php printf( __( 'Book Database requires PHP version 5.3 or greater. You have version %s. Please contact your web host to upgrade your version of PHP.', 'book-database' ), PHP_VERSION ); ?></p>
			</div>
			<?php
		}

		add_action( 'admin_notices', 'bdb_insufficient_php_version' );
	}

	return;
}

/**
 * Returns the main instance of Book_Database.
 *
 * @since 1.0.0
 * @return Book_Database
 */
function book_database() {
	$instance = Book_Database::instance();

	return $instance;
}

book_database();