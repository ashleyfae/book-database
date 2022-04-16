<?php
/**
 * HandleResponseErrors.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Actions;

use ContextWP\Helpers\Str;
use ContextWP\Http\Response;
use ContextWP\Repositories\ProductErrorsRepository;
use ContextWP\ValueObjects\ErrorConsequence;
use ContextWP\ValueObjects\Product;

class HandleResponseErrors
{
    /** @var ProductErrorsRepository $productErrorsRepository */
    protected $productErrorsRepository;

    /** @var Response $response API response */
    protected $response;

    public function __construct()
    {
        $this->productErrorsRepository = new ProductErrorsRepository();
    }

    /**
     * Parses errors out of the response and handles them accordingly for each product it affects.
     *
     * @param  Response  $response
     * @param  array  $products
     *
     * @return void
     */
    public function execute(Response $response, array $products): void
    {
        $this->response = $response;

        if ($code = $response->jsonKey('error_code')) {
            $this->addConsequenceCodeForAll($code, $products);
        } elseif ($response->jsonKey('errors')) {
            $this->addConsequenceCodeForAll(ErrorConsequence::ValidationError, $products);
        } elseif ($rejected = $response->jsonKey('rejected')) {
            $this->addIndividualProductConsequences($rejected);
        }
    }

    /**
     * Creates an ErrorConsequence object for a given product ID and error code.
     *
     * @since 1.0
     *
     * @param  string  $productId
     * @param  string  $errorCode
     *
     * @return ErrorConsequence
     */
    protected function makeConsequence(string $productId, string $errorCode): ErrorConsequence
    {
        return new ErrorConsequence($productId, $errorCode, Str::sanitize($this->response->responseBody));
    }

    /**
     * Adds the same consequence code for all products. This is called when the _entire_ request fails
     * and all products are affected.
     *
     * @todo Reduce duplication with {@see HandleResponseErrors::addIndividualProductConsequences()}
     *
     * @since 1.0
     *
     * @param  string  $errorCode
     * @param  array  $products
     */
    protected function addConsequenceCodeForAll(string $errorCode, array $products): void
    {
        $consequences = array_map(function (Product $product) use ($errorCode) {
            return $this->makeConsequence($product->productId, $errorCode);
        }, $products);

        $this->productErrorsRepository->lockProducts($consequences);
    }

    /**
     * Adds consequences for the products included in the errors array. This is called when only specific
     * products fail but the overall request succeeded.
     *
     * @todo Reduce duplication with {@see HandleResponseErrors::addConsequenceCodeForAll()}
     *
     * @since 1.0
     *
     * @param  array  $rejected
     */
    protected function addIndividualProductConsequences(array $rejected): void
    {
        $consequences = [];
        foreach ($rejected as $productId => $errorCode) {
            $consequences[] = $this->makeConsequence($productId, $errorCode);
        }

        $this->productErrorsRepository->lockProducts($consequences);
    }
}
