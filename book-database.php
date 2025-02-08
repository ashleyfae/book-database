<?php
/**
 * Plugin Name: Book Database
 * Plugin URI: https://shop.nosegraze.com/product/book-database/
 * Description: Maintain a database of books and reviews.
 * Version: 1.3.2
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
 * @copyright Copyright (c) 2023, Ashley Gibson
 * @license   GPL2+
 */

namespace Book_Database;

if (! defined('BDB_VERSION')) {
    define('BDB_VERSION', '1.3.2');
}
if (! defined('BDB_DIR')) {
    define('BDB_DIR', plugin_dir_path(__FILE__));
}
if (! defined('BDB_URL')) {
    define('BDB_URL', plugin_dir_url(__FILE__));
}
if (! defined('BDB_FILE')) {
    define('BDB_FILE', __FILE__);
}
if (! defined('NOSE_GRAZE_STORE_URL')) {
    define('NOSE_GRAZE_STORE_URL', 'https://shop.nosegraze.com');
}

/**
 * Require PHP 7.1+
 */
/**
 * Insufficient PHP version notice.
 *
 * @return void
 */
function insufficient_php_version()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            printf(
                __(
                    'Book Database requires PHP version 7.1 or greater. You have version %s. Please contact your web host to upgrade your version of PHP.',
                    'book-database'
                ),
                PHP_VERSION
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Returns the main instance of Book_Database.
 *
 * @return Plugin
 */
function book_database($abstract = null)
{
    $instance = Plugin::instance();

    if ($abstract !== null) {
        return $instance->make($abstract);
    }

    return $instance;
}

if (version_compare(PHP_VERSION, '8.0', '>=')) {
    require_once BDB_DIR.'vendor/autoload.php';
    book_database();
} else {
    add_action('admin_notices', __NAMESPACE__.'\insufficient_php_version');
}

/**
 * On activation, create an option. We'll use this as a flag to actually run our activation later.
 *
 * @see   Plugin::install()
 *
 * @since 1.0
 */
function activate()
{
    add_option('bdb_run_activation', date('Y-m-d H:i:s'));
}

register_activation_hook(__FILE__, __NAMESPACE__.'\activate');
