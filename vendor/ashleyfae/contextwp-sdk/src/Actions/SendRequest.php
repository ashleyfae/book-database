<?php
/**
 * SendRequest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Actions;

use ContextWP\Exceptions\MissingPublicKeyException;
use ContextWP\Http\Request;
use ContextWP\Http\Response;
use ContextWP\Traits\Makeable;
use ContextWP\ValueObjects\Environment;
use Exception;

/**
 * Handles executing a request to send check-in data for the supplied products.
 */
class SendRequest
{
    use Makeable;

    /**
     * Sends the check-in request.
     *
     * @param  string  $publicKey
     * @param  array  $products
     *
     * @return Response
     * @throws Exception|MissingPublicKeyException
     */
    public function execute(string $publicKey, array $products): Response
    {
        return Request::make()
            ->setUrl($this->getApiUrl())
            ->setPublicKey($publicKey)
            ->setEnvironment(Environment::make())
            ->setProducts($products)
            ->execute();
    }

    /**
     * Returns the API URL.
     *
     * @return string
     */
    protected function getApiUrl(): string
    {
        if (defined('CONTEXTWP_API_URL')) {
            return (string) CONTEXTWP_API_URL;
        } else {
            return 'https://api.contextwp.com/v1/checkin';
        }
    }
}
