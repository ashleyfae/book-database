<?php
/**
 * Request.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Http;

use ContextWP\Exceptions\MissingPublicKeyException;
use ContextWP\Traits\Makeable;
use ContextWP\ValueObjects\Environment;
use ContextWP\ValueObjects\Product;
use Exception;

/**
 * HTTP request helper for sending check-in data to the ContextWP API.
 *
 * @since 1.0
 */
class Request
{
    use Makeable;

    /** @var string URL to make the request to */
    protected $url;

    /** @var string $publicKey customer's public key */
    protected $publicKey;

    /** @var Environment site environment */
    protected $environment;

    /** @var Product[] products included in this request */
    protected $products;

    /**
     * Sets the URL.
     *
     * @since 1.0
     *
     * @param  string  $url
     *
     * @return $this
     */
    public function setUrl(string $url): Request
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Sets the public key.
     *
     * @since 1.0
     *
     * @param  string  $publicKey
     *
     * @return $this
     */
    public function setPublicKey(string $publicKey): Request
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Sets the environment.
     *
     * @since 1.0
     *
     * @param  Environment  $environment
     *
     * @return $this
     */
    public function setEnvironment(Environment $environment): Request
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Sets the products.
     *
     * @since 1.0
     *
     * @param  array  $products
     *
     * @return $this
     */
    public function setProducts(array $products): Request
    {
        $this->products = $products;

        return $this;
    }

    /**
     * Executes the request.
     *
     * @since 1.0
     *
     * @return Response
     * @throws Exception
     */
    public function execute(): Response
    {
        return $this->makeResponse(wp_remote_post(
            $this->url,
            [
                'headers' => $this->makeHeaders(),
                'body'    => json_encode($this->makeBody()),
            ]
        ));
    }

    /**
     * Builds the headers for our request.
     *
     * @since 1.0
     *
     * @return string[]
     * @throws MissingPublicKeyException
     */
    protected function makeHeaders(): array
    {
        if (empty($this->publicKey)) {
            throw new MissingPublicKeyException();
        }

        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'Public-Key'   => $this->publicKey,
        ];
    }

    /**
     * Builds the body arguments.
     *
     * @since 1.0
     *
     * @return array
     * @throws Exception
     */
    protected function makeBody(): array
    {
        return [
            'environment' => $this->environment->toArray(),
            'products'    => array_map(function (Product $product) {
                return $product->toArray();
            }, $this->products)
        ];
    }

    /**
     * Creates a response object from the response mess that WordPress gives us.
     *
     * @since 1.0
     *
     * @param  array|WP_Error  $response
     *
     * @return Response
     */
    protected function makeResponse($response): Response
    {
        if (is_wp_error($response)) {
            return Response::makeFromWpError($response);
        }

        return new Response(
            (int) wp_remote_retrieve_response_code($response),
            wp_remote_retrieve_body($response) ?: null
        );
    }
}
