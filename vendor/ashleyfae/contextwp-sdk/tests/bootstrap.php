<?php
/**
 * bootstrap.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */


const ABSPATH = 'foo/bar';

require_once dirname(__DIR__).'/vendor/autoload.php';

WP_Mock::setUsePatchwork( true);
WP_Mock::bootstrap();
