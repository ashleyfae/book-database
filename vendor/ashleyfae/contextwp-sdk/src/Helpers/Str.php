<?php
/**
 * Str.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Helpers;

class Str
{
    /**
     * Sanitizes a string.
     *
     * @since 1.0
     *
     * @param  mixed  $string
     *
     * @return string
     */
    public static function sanitize($string): string
    {
        $string = strip_tags((string) $string);

        return function_exists('sanitize_text_field') ? sanitize_text_field($string) : $string;
    }

    /**
     * Determines if the provided string is a UUID.
     *
     * @since 1.0
     *
     * @param  string  $string
     *
     * @return bool
     */
    public static function isUuid(string $string): bool
    {
        return preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $string) === 1;
    }

    /**
     * Cuts a string to the supplied number of characters, if the string is longer than the max characters.
     *
     * @param  string  $string
     * @param  int  $maxLength
     *
     * @return string
     */
    public static function maxChars(string $string, int $maxLength): string
    {
        return substr($string, 0, $maxLength);
    }
}
