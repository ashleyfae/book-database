<?php
/**
 * Response.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Http;

use WP_Error;

class Response
{
    /** @var int HTTP response code */
    public $responseCode;

    /** @var string|null response body */
    public $responseBody;

    /** @var array|null $responseBodyJson json-decoded body */
    protected $responseBodyJson = null;

    public function __construct(int $responseCode, ?string $body = null)
    {
        $this->responseCode = $responseCode;
        $this->responseBody = $body;
    }

    /**
     * Creates a new response object from a WP_Error object.
     *
     * @param  WP_Error  $error
     *
     * @return Response
     */
    public static function makeFromWpError(WP_Error $error): Response
    {
        return new static(503, $error->get_error_message() ?: null);
    }

    /**
     * If the response is okay. This does NOT necessarily mean every single product got a check-in, but
     * it means we passed any authorization requirements, and were able to talk to the site.
     *
     * @return bool
     */
    public function isOk(): bool
    {
        return $this->responseCode >= 200 && $this->responseCode < 300;
    }

    /**
     * Determines if we have any errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->jsonExists('error_code') ||
            $this->jsonExists('errors') ||
            ! empty($this->jsonKey('rejected'));
    }

    /**
     * If the service is unavailable (meaning subsequent API requests should be cancelled).
     *
     * @return bool
     */
    public function serviceIsUnavailable(): bool
    {
        return $this->responseCode >= 500 && $this->responseCode < 600;
    }

    /**
     * JSON-decodes the response body and returns it.
     *
     * @since 1.0
     *
     * @return array
     */
    protected function getJson(): array
    {
        if (! is_null($this->responseBodyJson)) {
            return $this->responseBodyJson;
        }

        $this->responseBodyJson = json_decode($this->responseBody, true);

        if (! is_array($this->responseBodyJson)) {
            $this->responseBodyJson = [];
        }

        return $this->responseBodyJson;
    }

    /**
     * Returns a key from the body.
     *
     * @since 1.0
     *
     * @param  string  $key  Key to retrieve.
     * @param  mixed  $default  The value to return if key is not set.
     *
     * @return mixed|null
     */
    public function jsonKey(string $key, $default = null)
    {
        return $this->getJson()[$key] ?? $default;
    }

    /**
     * Determines if the supplied key exists in the JSON body.
     *
     * @since 1.0
     *
     * @param  string  $key
     *
     * @return bool
     */
    public function jsonExists(string $key): bool
    {
        return array_key_exists($key, $this->getJson());
    }
}
