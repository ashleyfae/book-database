<?php
/**
 * Plugin.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database;

use Book_Database\Container\Container;
use Book_Database\Container\Exceptions\BindingResolutionException;
use Book_Database\Database\Authors\AuthorsTable;
use Book_Database\Database\BookAuthor\BookAuthorTable;
use Book_Database\Database\BookLinks\BookLinksTable;
use Book_Database\Database\Books\BookMetaTable;
use Book_Database\Database\Books\BooksTable;
use Book_Database\Database\BookTaxonomies\BookTaxonomiesTable;
use Book_Database\Database\BookTerm\BookTermRelationshipsTable;
use Book_Database\Database\BookTerms\BookTermsTable;
use Book_Database\Database\Editions\EditionsTable;
use Book_Database\Database\ReadingLogs\ReadingLogsTable;
use Book_Database\Database\Retailers\RetailersTable;
use Book_Database\Database\Reviews\ReviewMetaTable;
use Book_Database\Database\Reviews\ReviewsTable;
use Book_Database\Database\Series\SeriesTable;
use Book_Database\Exceptions\Exception;
use Book_Database\Helpers\HTML;
use Book_Database\ServiceProviders\ApiServiceProvider;
use Book_Database\ServiceProviders\AppServiceProvider;
use Book_Database\ServiceProviders\LegacyServiceProvider;
use Book_Database\ServiceProviders\ServiceProvider;
use Book_Database\ServiceProviders\ShortcodeServiceProvider;

final class Plugin
{

    /**
     * @var Plugin
     */
    private static $instance;

    /**
     * @var Container
     * @since 1.3
     */
    private $container;

    /**
     * @var string[]
     * @since 1.3
     */
    private $serviceProviders = [
        LegacyServiceProvider::class,
        AppServiceProvider::class,
        ApiServiceProvider::class,
        ShortcodeServiceProvider::class,
    ];

    /**
     * @var bool
     * @since 1.3
     */
    private $serviceProvidersLoaded = false;

    /**
     * Array of custom table objects
     *
     * @var array
     */
    private $tables = array();

    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * Plugin instance.
     *
     * @return Plugin Instance of Plugin class
     */
    public static function instance(): Plugin
    {
        // Return if already instantiated
        if (self::isInstantiated()) {
            return self::$instance;
        }

        // Set up the singleton.
        self::$instance = new Plugin();

        // Bootstrap
        self::$instance->setup_application();

        add_action('admin_init', array(self::$instance, 'install'), 11);

        return self::$instance;
    }

    /**
     * Magic methods are passed to the service container.
     *
     * @since 1.3
     *
     * @param  string  $name
     * @param  mixed  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->container, $name], $arguments);
    }

    /**
     * Properties are passed to the service container.
     *
     * @since 1.3
     *
     * @param  string  $propertyName
     *
     * @return mixed|object
     * @throws BindingResolutionException
     */
    public function __get($propertyName)
    {
        return $this->container->get($propertyName);
    }

    /**
     * Whether the main class has been instantiated or not.
     *
     * @return bool
     */
    private static function isInstantiated(): bool
    {
        return ! empty(self::$instance) && self::$instance instanceof Plugin;
    }

    /**
     * Set up custom database tables
     */
    private function setup_application()
    {
        self::$instance->loadServiceProviders();

        self::$instance->tables = array(
            'authors'                   => new AuthorsTable(),
            'book_author_relationships' => new BookAuthorTable(),
            'book_links'                => new BookLinksTable(),
            'book_taxonomies'           => new BookTaxonomiesTable(),
            'book_term_relationships'   => new BookTermRelationshipsTable(),
            'book_terms'                => new BookTermsTable(),
            'books'                     => new BooksTable(),
            'book_meta'                 => new BookMetaTable(),
            'editions'                  => new EditionsTable(),
            'reading_log'               => new ReadingLogsTable(),
            'retailers'                 => new RetailersTable(),
            'reviews'                   => new ReviewsTable(),
            'review_meta'               => new ReviewMetaTable(),
            'series'                    => new SeriesTable(),
        );

    }

    private function loadServiceProviders(): void
    {
        if ($this->serviceProvidersLoaded) {
            return;
        }

        $providers = [];

        foreach ($this->serviceProviders as $serviceProvider) {
            if (! is_subclass_of($serviceProvider, ServiceProvider::class)) {
                throw new \InvalidArgumentException(sprintf(
                    '%s class must implement the %s interface.',
                    $serviceProvider,
                    ServiceProvider::class
                ));
            }

            /** @var ServiceProvider $serviceProvider */
            $serviceProvider = new $serviceProvider;
            $serviceProvider->register();
            $providers[] = $serviceProvider;
        }

        foreach ($providers as $serviceProvider) {
            $serviceProvider->boot();
        }

        $this->serviceProvidersLoaded = true;
    }

    /**
     * Get a table object by its key
     *
     * @param  string  $table_key  Table key.  One of:
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
    public function get_table(string $table_key)
    {
        return array_key_exists($table_key, self::$instance->tables) ? self::$instance->tables[$table_key] : false;
    }

    /**
     * Returns an array of all registered tables
     *
     * @return BerlinDB\Database\Table[]
     */
    public function get_tables(): array
    {
        return self::$instance->tables;
    }

    /**
     * Get the HTML helper class
     *
     * @return HTML
     */
    public function get_html(): HTML
    {
        return $this->html;
    }

    /**
     * Run installation
     *
     *      - Install default taxonomies.
     *      - Add rewrite tags/rules and flush
     */
    public function install(): void
    {
        if (! get_option('bdb_run_activation')) {
            return;
        }

        /**
         * Add default taxonomies.
         */
        if (! $this->get_table('book_taxonomies')->exists()) {
            $this->get_table('book_taxonomies')->install();
        }

        $default_taxonomies = array(
            'publisher' => array(
                'slug'   => 'publisher',
                'name'   => esc_html__('Publisher', 'book-database'),
                'format' => 'text' // text, checkbox
            ),
            'genre'     => array(
                'slug'   => 'genre',
                'name'   => esc_html__('Genre', 'book-database'),
                'format' => 'text'
            ),
            'source'    => array(
                'slug'   => 'source',
                'name'   => esc_html__('Source', 'book-database'),
                'format' => 'checkbox'
            )
        );

        foreach ($default_taxonomies as $taxonomy) {
            if (get_book_taxonomy_by('slug', $taxonomy['slug'])) {
                continue;
            }

            try {
                add_book_taxonomy($taxonomy);
            } catch (Exception $e) {

            }
        }

        /**
         * Add rewrite tags/rules and flush
         */
        add_rewrite_tags();
        add_rewrite_rules();
        flush_rewrite_rules(true);

        /**
         * Add capabilities
         */
        $role = get_role('administrator');

        foreach (get_book_capabilities() as $capability) {
            $role->add_cap($capability, true);
        }

        /**
         * Set version number
         */
        update_option('bdb_version', BDB_VERSION);

        if (! get_option('bdb_install_date')) {
            update_option('bdb_install_date', date('Y-m-d H:i:s'), false);
        }

        delete_option('bdb_run_activation');
    }

}
