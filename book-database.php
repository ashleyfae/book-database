<?php
/**
 * Plugin Name: Book Database
 * Plugin URI: https://shop.nosegraze.com/product/book-database/
 * Description: Maintain a database of books and reviews.
 * Version: 1.2.2
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
 * @package   book-database
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'BDB_VERSION' ) ) {
	define( 'BDB_VERSION', '1.2.2' );
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

/**
 * Class Book_Database
 *
 * @package Book_Database
 */
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

		add_action( 'admin_init', array( self::$instance, 'install' ), 11 );

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
		require_once BDB_DIR . 'includes/database/engine/series.php';
		require_once BDB_DIR . 'includes/database/engine/tax.php';
		require_once BDB_DIR . 'includes/database/engine/author.php';
		require_once BDB_DIR . 'includes/database/engine/join.php';
		require_once BDB_DIR . 'includes/database/engine/book.php';
		require_once BDB_DIR . 'includes/database/engine/edition.php';
		require_once BDB_DIR . 'includes/database/engine/reading-log.php';
		require_once BDB_DIR . 'includes/database/engine/class-where-clause.php';
		require_once BDB_DIR . 'includes/database/sanitization.php';

		// Database - authors
		require_once BDB_DIR . 'includes/database/authors/class-authors-table.php';
		require_once BDB_DIR . 'includes/database/authors/class-authors-schema.php';
		require_once BDB_DIR . 'includes/database/authors/class-authors-query.php';

		// Database - book_author_relationships
		require_once BDB_DIR . 'includes/database/book-author-relationships/class-book-author-relationships-table.php';
		require_once BDB_DIR . 'includes/database/book-author-relationships/class-book-author-relationships-schema.php';
		require_once BDB_DIR . 'includes/database/book-author-relationships/class-book-author-relationships-query.php';

		// Database - books
		require_once BDB_DIR . 'includes/database/books/class-books-table.php';
		require_once BDB_DIR . 'includes/database/books/class-books-schema.php';
		require_once BDB_DIR . 'includes/database/books/class-books-query.php';
		require_once BDB_DIR . 'includes/database/books/class-book-meta-table.php';

		// Database - book_links
		require_once BDB_DIR . 'includes/database/book-links/class-book-links-table.php';
		require_once BDB_DIR . 'includes/database/book-links/class-book-links-schema.php';
		require_once BDB_DIR . 'includes/database/book-links/class-book-links-query.php';

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

		// Database - owned_editions
		require_once BDB_DIR . 'includes/database/editions/class-editions-table.php';
		require_once BDB_DIR . 'includes/database/editions/class-editions-schema.php';
		require_once BDB_DIR . 'includes/database/editions/class-editions-query.php';

		// Database - reading_log
		require_once BDB_DIR . 'includes/database/reading-logs/class-reading-logs-table.php';
		require_once BDB_DIR . 'includes/database/reading-logs/class-reading-logs-schema.php';
		require_once BDB_DIR . 'includes/database/reading-logs/class-reading-logs-query.php';

		// Database - retailers
		require_once BDB_DIR . 'includes/database/retailers/class-retailers-table.php';
		require_once BDB_DIR . 'includes/database/retailers/class-retailers-schema.php';
		require_once BDB_DIR . 'includes/database/retailers/class-retailers-query.php';

		// Database - reviews
		require_once BDB_DIR . 'includes/database/reviews/class-reviews-table.php';
		require_once BDB_DIR . 'includes/database/reviews/class-reviews-schema.php';
		require_once BDB_DIR . 'includes/database/reviews/class-reviews-query.php';
		require_once BDB_DIR . 'includes/database/reviews/class-review-meta-table.php';

		// Database - series
		require_once BDB_DIR . 'includes/database/series/class-series-table.php';
		require_once BDB_DIR . 'includes/database/series/class-series-schema.php';
		require_once BDB_DIR . 'includes/database/series/class-series-query.php';

		// Analytics
		require_once BDB_DIR . 'includes/analytics/abstract-class-dataset.php';
		require_once BDB_DIR . 'includes/analytics/analytics-functions.php';
		require_once BDB_DIR . 'includes/analytics/graphs/class-graph.php';
		require_once BDB_DIR . 'includes/analytics/graphs/class-bar-graph.php';
		require_once BDB_DIR . 'includes/analytics/graphs/class-horizontal-bar-graph.php';
		require_once BDB_DIR . 'includes/analytics/graphs/class-pie-chart.php';
		require_once BDB_DIR . 'includes/analytics/graphs/class-scatter-chart.php';
		$datasets = array(
			'reading-overview', 'number-different-series-read', 'number-standalones-read', 'pages-read',
			'number-different-authors-read', 'number-reviews-written', 'average-rating', 'reading-track',
			'books-per-year', 'most-read-genres', 'pages-breakdown', 'ratings-breakdown', 'highest-rated-books',
			'lowest-rated-books', 'format-breakdown', 'average-days-finish-book', 'shortest-book-read', 'longest-book-read',
			'number-editions', 'edition-format-breakdown', 'editions-over-time', 'reviews-over-time', 'reviews-written',
			'books-read-over-time', 'terms-breakdown', 'number-signed-editions', 'edition-genre-breakdown',
			'edition-source-breakdown', 'average-days-acquired-to-read', 'number-books-added', 'number-series-books-added',
			'number-standalones-added', 'number-distinct-authors-added', 'library-genre-breakdown', 'library-book-releases',
			'books-read-by-publication-year',
		);
		foreach ( $datasets as $dataset ) {
			if ( file_exists( BDB_DIR . 'includes/analytics/datasets/class-' . $dataset . '.php' ) ) {
				require_once BDB_DIR . 'includes/analytics/datasets/class-' . $dataset . '.php';
			}
		}

		// Authors
		require_once BDB_DIR . 'includes/authors/class-author.php';
		require_once BDB_DIR . 'includes/authors/author-functions.php';

		// Blocks
		require_once BDB_DIR . 'includes/blocks.php';

		// Book Author Relationships
		require_once BDB_DIR . 'includes/book-author-relationships/class-book-author-relationship.php';
		require_once BDB_DIR . 'includes/book-author-relationships/book-author-relationship-actions.php';
		require_once BDB_DIR . 'includes/book-author-relationships/book-author-relationship-functions.php';

		// Books
		require_once BDB_DIR . 'includes/books/class-book.php';
		require_once BDB_DIR . 'includes/books/class-book-layout.php';
		require_once BDB_DIR . 'includes/books/book-functions.php';
		require_once BDB_DIR . 'includes/books/book-layout-functions.php';
		require_once BDB_DIR . 'includes/books/book-meta.php';

		// Book Links
		require_once BDB_DIR . 'includes/book-links/class-book-link.php';
		require_once BDB_DIR . 'includes/book-links/book-link-functions.php';

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

		// Editions
		require_once BDB_DIR . 'includes/editions/class-edition.php';
		require_once BDB_DIR . 'includes/editions/edition-functions.php';

		// Ratings
		require_once BDB_DIR . 'includes/ratings/class-rating.php';
		require_once BDB_DIR . 'includes/ratings/rating-functions.php';

		// Reading Logs
		require_once BDB_DIR . 'includes/reading-logs/class-reading-log.php';
		require_once BDB_DIR . 'includes/reading-logs/reading-log-functions.php';

		// Retailers
		require_once BDB_DIR . 'includes/retailers/class-retailer.php';
		require_once BDB_DIR . 'includes/retailers/retailer-functions.php';

		// Reviews
		require_once BDB_DIR . 'includes/reviews/class-review.php';
		require_once BDB_DIR . 'includes/reviews/review-actions.php';
		require_once BDB_DIR . 'includes/reviews/review-functions.php';
		require_once BDB_DIR . 'includes/reviews/review-meta.php';

		// Series
		require_once BDB_DIR . 'includes/series/class-series.php';
		require_once BDB_DIR . 'includes/series/series-functions.php';

		// REST API
		require_once BDB_DIR . 'includes/rest-api/class-rest-api.php';
		require_once BDB_DIR . 'includes/rest-api/abstract-class-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-analytics-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-author-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-book-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-book-link-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-book-term-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-edition-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-reading-log-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-retailer-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-review-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-series-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-taxonomy-controller.php';
		require_once BDB_DIR . 'includes/rest-api/v1/class-utility-controller.php';

		// Widgets
		require_once BDB_DIR . 'includes/widgets/Reading_Log.php';
		require_once BDB_DIR . 'includes/widgets/Reviews.php';

		// Misc.
		require_once BDB_DIR . 'includes/capabilities.php';
		require_once BDB_DIR . 'includes/class-analytics.php';
		require_once BDB_DIR . 'includes/class-book-reviews-query.php';
		require_once BDB_DIR . 'includes/class-book-grid-query.php';
		require_once BDB_DIR . 'includes/class-html.php';
		require_once BDB_DIR . 'includes/misc-functions.php';
		require_once BDB_DIR . 'includes/rewrites.php';
		require_once BDB_DIR . 'includes/shortcodes.php';
		require_once BDB_DIR . 'includes/template-functions.php';

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once BDB_DIR . 'includes/class-cli.php';
		}

	}

	/**
	 * Include admin files
	 */
	private function include_admin() {

		require_once BDB_DIR . 'includes/admin/abstract-class-list-table.php';
		require_once BDB_DIR . 'includes/admin/admin-assets.php';
		require_once BDB_DIR . 'includes/admin/admin-bar.php';
		require_once BDB_DIR . 'includes/admin/admin-notices.php';
		require_once BDB_DIR . 'includes/admin/admin-pages.php';

		// Analytics
		require_once BDB_DIR . 'includes/admin/analytics/analytics-page.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/overview.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/library.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/reading.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/ratings.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/editions.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/reviews.php';
		require_once BDB_DIR . 'includes/admin/analytics/tabs/terms.php';

		// Authors
		require_once BDB_DIR . 'includes/admin/authors/author-actions.php';
		require_once BDB_DIR . 'includes/admin/authors/author-functions.php';
		require_once BDB_DIR . 'includes/admin/authors/authors-page.php';

		// Book Terms
		require_once BDB_DIR . 'includes/admin/book-terms/book-term-actions.php';
		require_once BDB_DIR . 'includes/admin/book-terms/book-term-functions.php';
		require_once BDB_DIR . 'includes/admin/book-terms/book-terms-page.php';

		// Books
		require_once BDB_DIR . 'includes/admin/books/book-actions.php';
		require_once BDB_DIR . 'includes/admin/books/book-functions.php';
		require_once BDB_DIR . 'includes/admin/books/books-page.php';
		require_once BDB_DIR . 'includes/admin/books/edit-book-fields.php';

		// Dashboard
		require_once BDB_DIR . 'includes/admin/dashboard/widgets.php';

		// Editions
		require_once BDB_DIR . 'includes/admin/editions/edition-actions.php';

		// Licensing
		require_once BDB_DIR . 'vendor/class-edd-sl-plugin-updater.php';
		require_once BDB_DIR . 'includes/admin/licensing/class-license-key.php';
		require_once BDB_DIR . 'includes/admin/licensing/license-actions.php';

		// Posts
		require_once BDB_DIR . 'includes/admin/posts/post-actions.php';

		// Reading Logs
		require_once BDB_DIR . 'includes/admin/reading-logs/reading-log-actions.php';

		// Reviews
		require_once BDB_DIR . 'includes/admin/reviews/review-actions.php';
		require_once BDB_DIR . 'includes/admin/reviews/review-fields.php';
		require_once BDB_DIR . 'includes/admin/reviews/review-functions.php';
		require_once BDB_DIR . 'includes/admin/reviews/reviews-page.php';

		// Series
		require_once BDB_DIR . 'includes/admin/series/series-actions.php';
		require_once BDB_DIR . 'includes/admin/series/series-functions.php';
		require_once BDB_DIR . 'includes/admin/series/series-page.php';

		// Settings
		require_once BDB_DIR . 'includes/admin/settings/book-layout-functions.php';
		require_once BDB_DIR . 'includes/admin/settings/register-settings.php';
		require_once BDB_DIR . 'includes/admin/settings/display-settings.php';

	}

	/**
	 * Include front-end files
	 */
	private function include_frontend() {
		require_once BDB_DIR . 'includes/assets.php';
	}

	/**
	 * Set up custom database tables
	 */
	private function setup_application() {

		self::$instance->tables = array(
			'authors'                   => new Authors_Table(),
			'book_author_relationships' => new Book_Author_Relationships_Table(),
			'book_links'                => new Book_Links_Table(),
			'book_taxonomies'           => new Book_Taxonomies_Table(),
			'book_term_relationships'   => new Book_Term_Relationships_Table(),
			'book_terms'                => new Book_Terms_Table(),
			'books'                     => new Books_Table(),
			'book_meta'                 => new Book_Meta_Table(),
			'editions'                  => new Editions_Table(),
			'reading_log'               => new Reading_Logs_Table(),
			'retailers'                 => new Retailers_Table(),
			'reviews'                   => new Reviews_Table(),
			'review_meta'               => new Review_Meta_Table(),
			'series'                    => new Series_Table(),
		);

		self::$instance->rest_api = new REST_API();
		self::$instance->html     = new HTML();

	}

	/**
	 * Get a table object by its key
	 *
	 * @param string $table_key Table key.  One of:
	 *                          'authors',
	 *                          'book_author_relationships',
	 *                          'book_links',
	 *                          'book_taxonomies',
	 *                          'book_term_relationships',
	 *                          'book_terms'
	 *                          'books'
	 *                          'book_meta',
	 *                          'editions'
	 *                          'reading_log'
	 *                          'retailers',
	 *                          'reviews',
	 *                          'review_meta',
	 *                          'series'
	 *
	 * @return BerlinDB\Database\Table|false
	 */
	public function get_table( $table_key ) {
		return array_key_exists( $table_key, self::$instance->tables ) ? self::$instance->tables[ $table_key ] : false;
	}

	/**
	 * Returns an array of all registered tables
	 *
	 * @return BerlinDB\Database\Table[]
	 */
	public function get_tables() {
		return self::$instance->tables;
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
	 *      - Add rewrite tags/rules and flush
	 */
	public function install() {

		if ( ! get_option( 'bdb_run_activation' ) ) {
			return;
		}

		/**
		 * Add default taxonomies.
		 */
		if ( ! $this->get_table( 'book_taxonomies' )->exists() ) {
			$this->get_table( 'book_taxonomies' )->install();
		}

		$default_taxonomies = array(
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
			if ( get_book_taxonomy_by( 'slug', $taxonomy['slug'] ) ) {
				continue;
			}

			try {
				add_book_taxonomy( $taxonomy );
			} catch ( Exception $e ) {

			}
		}

		/**
		 * Add rewrite tags/rules and flush
		 */
		add_rewrite_tags();
		add_rewrite_rules();
		flush_rewrite_rules( true );

		/**
		 * Add capabilities
		 */
		$role = get_role( 'administrator' );

		foreach ( get_book_capabilities() as $capability ) {
			$role->add_cap( $capability, true );
		}

		/**
		 * Set version number
		 */
		update_option( 'bdb_version', BDB_VERSION );

		if ( ! get_option( 'bdb_install_date' ) ) {
			update_option( 'bdb_install_date', date( 'Y-m-d H:i:s' ), false );
		}

		delete_option( 'bdb_run_activation' );

	}

}


/**
 * Require PHP 7.0+
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

/**
 * On activation, create an option. We'll use this as a flag to actually run our activation later.
 *
 * @see   Book_Database::install()
 *
 * @since 1.0
 */
function activate() {
	add_option( 'bdb_run_activation', date( 'Y-m-d H:i:s' ) );
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activate' );
