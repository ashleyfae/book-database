<?php
/**
 * Loader.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP;

class Loader
{
    /** @var Loader */
    protected static $instance;

    /** @var array All registered versions of this SDK. */
    protected $registeredSdks = [];

    /** @var array Information about the latest version of the SDK (version and path to file). */
    protected $latestSdk = [];

    /**
     * Returns the loader instance.
     *
     * @since 1.0
     */
    public static function instance(): Loader
    {
        if (static::$instance instanceof Loader) {
            return static::$instance;
        }

        static::$instance = new static;
        static::$instance->init();

        return static::$instance;
    }

    /**
     * Sets up the hook to load the SDK.
     *
     * @since 1.0
     */
    protected function init(): void
    {
        add_action('after_setup_theme', [$this, 'setAndLoadLatest'], PHP_INT_MAX);
    }

    /**
     * Determines the latest version of the SDK and loads it.
     *
     * @internal
     * @since 1.0
     */
    public function setAndLoadLatest(): void
    {
        foreach ($this->registeredSdks as $registeredSdk) {
            if ($this->isLaterVersion($registeredSdk)) {
                $this->latestSdk = $registeredSdk;
            }
        }

        $this->loadLatestSdk();
    }

    /**
     * Loads the latest version of the SDK.
     *
     * @since 1.0
     */
    protected function loadLatestSdk(): void
    {
        if (! empty($this->latestSdk['path']) && file_exists($this->latestSdk['path'])) {
            require_once $this->latestSdk['path'];

            if (class_exists(SDK::class) && ! did_action('contextwp_sdk_loaded')) {
                /**
                 * Triggers after the SDK has been loaded.
                 *
                 * @since 1.0
                 *
                 * @param  SDK  $sdk
                 */
                do_action('contextwp_sdk_loaded', SDK::instance());
            }
        }
    }

    /**
     * Determines whether the provided SDK is later than the currently set.
     *
     * @since 1.0
     *
     * @param  array  $sdk
     *
     * @return bool
     */
    protected function isLaterVersion(array $sdk): bool
    {
        if (empty($sdk['version']) || empty($sdk['path'])) {
            return false;
        }

        if (empty($this->latestSdk)) {
            return true;
        }

        return version_compare($sdk['version'], $this->latestSdk['version'], '>');
    }


    /**
     * Registers a version of the SDK.
     *
     * @since 1.0
     *
     * @param  string  $version  Version of the SDK being registered.
     * @param  string  $pathToSdk  Path to the `SDK` class file.
     *
     * @return $this
     */
    public function registerSdk(string $version, string $pathToSdk): Loader
    {
        $this->registeredSdks[] = [
            'version' => $version,
            'path'    => $pathToSdk,
        ];

        return $this;
    }
}
