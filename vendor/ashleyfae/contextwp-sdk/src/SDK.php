<?php
/**
 * SDK.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP;

use ContextWP\Actions\HandleCronEvent;
use ContextWP\Cli\CliProvider;
use ContextWP\Database\TableManager;
use ContextWP\Registries\ProductRegistry;
use ContextWP\ValueObjects\Product;

class SDK
{
    /** @var SDK */
    protected static $instance;

    /** @var string Current version. */
    protected static $version = '1.0';

    /** @var string Path to the SDK directory. */
    public static $dir;

    /** @var string[] components to initialize and boot */
    protected $components = [
        TableManager::class,
        HandleCronEvent::class,
        CliProvider::class,
    ];

    /**
     * Returns an instance of the SDK.
     *
     * @since 1.0
     *
     * @return SDK
     */
    public static function instance(): SDK
    {
        if (static::$instance instanceof SDK) {
            return self::$instance;
        }

        static::$instance = new static;
        static::$instance->init();

        return static::$instance;
    }

    /**
     * Initializes things.
     *
     * @since 1.0
     */
    protected function init(): void
    {
        $this->loadComponents();
    }

    /**
     * Loads the components. This creates a new instance of the class and calls the `load()` method to
     * do any bootstrapping.
     *
     * @since 1.0
     */
    protected function loadComponents(): void
    {
        foreach ($this->components as $component) {
            (new $component)->load();
        }
    }

    /**
     * Retrieves the SDK version.
     *
     * @since 1.0
     *
     * @return string
     */
    public static function getVersion(): string
    {
        return static::$version;
    }

    /**
     * Registers a new product.
     *
     * @param  Product  $product
     *
     * @return $this
     * @throws Exceptions\InvalidProductException
     */
    public function register(Product $product): SDK
    {
        ProductRegistry::getInstance()->add($product);

        return $this;
    }
}
