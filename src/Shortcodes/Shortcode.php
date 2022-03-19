<?php
/**
 * Shortcode.php
 *
 * @package   book-database
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 * @since     1.3
 */

namespace Book_Database\Shortcodes;

interface Shortcode
{
    /**
     * Shortcode tag name.
     *
     * @since 1.3
     *
     * @return string
     */
    public static function tag(): string;

    /**
     * Callback for rendering the shortcode.
     *
     * @since 1.3
     *
     * @param  array  $atts
     * @param  string  $content
     *
     * @return string
     */
    public function make($atts, $content = ''): string;

}
