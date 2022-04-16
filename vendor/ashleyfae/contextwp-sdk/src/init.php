<?php
/**
 * Registers this version of the SDK.
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP;

if (! defined('CONTEXTWP_TESTS')) {
    Loader::instance()->registerSdk('1.0', __DIR__.'/SDK.php');
}
