<?php
/**
 * SendCheckIns.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Actions;

use ContextWP\Exceptions\ServiceUnavailableException;
use ContextWP\Exceptions\MissingPublicKeyException;
use ContextWP\Exceptions\NoProductsToCheckInException;
use ContextWP\Http\Response;
use ContextWP\Registries\ProductRegistry;
use ContextWP\Repositories\ProductErrorsRepository;
use ContextWP\Traits\Loggable;
use ContextWP\ValueObjects\Product;

class SendCheckIns
{
    use Loggable;

    /** @var bool $applyProductFilters whether to filter out invalid (failed/rejected) products */
    protected $applyProductFilters = true;

    /** @var ProductErrorsRepository $productErrorsRepository */
    protected $productErrorsRepository;

    /** @var HandleResponseErrors $responseErrorHandler */
    protected $responseErrorHandler;

    /** @var ProductRegistry $productRegistry */
    protected $productRegistry;

    /** @var array $lockedProductIds IDs of all products that are locked */
    protected $lockedProductIds = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productErrorsRepository = new ProductErrorsRepository();
        $this->responseErrorHandler    = new HandleResponseErrors();
        $this->productRegistry         = ProductRegistry::getInstance();
    }

    /**
     * Disables the process of filtering out invalid (recently failed/rejected) products.
     *
     * @since 1.0
     *
     * @return $this
     */
    public function withoutFiltering(): SendCheckIns
    {
        $this->applyProductFilters = false;

        return $this;
    }

    /**
     * Executes the check-ins for all (valid) products.
     * This process is done in batches: one request per public key (where each request may contain multiple
     * products).
     *
     * @since 1.0
     */
    public function execute()
    {
        $this->productErrorsRepository->deleteExpiredErrors();

        if ($this->applyProductFilters) {
            $this->lockedProductIds = $this->productErrorsRepository->getLockedProductIds();
        }

        $productGroups = $this->productRegistry->getProducts();

        $this->log(sprintf('%d product groups to check in.', count($productGroups)));

        try {
            foreach ($productGroups as $publicKey => $products) {
                $this->log("Sending check-ins for PK: {$publicKey}");

                try {
                    $this->handleGroupResponse(
                        $this->executeRequestGroup($publicKey, $products),
                        $products
                    );
                } catch (NoProductsToCheckInException $e) {
                    // this is fine
                } catch (MissingPublicKeyException $e) {
                    // I mean, it's not really fine, but we can continue with other groups
                    // @todo Ideally report this somehow :thinking:
                }
            }
        } catch (ServiceUnavailableException $e) {
            $this->handleServiceUnavailable();
        }
    }

    /**
     * Executes a "request group". This is one API request for a collection of products.
     *
     * @since 1.0
     *
     * @param  string  $publicKey
     * @param  array  $products
     *
     * @return Response
     * @throws NoProductsToCheckInException|MissingPublicKeyException
     */
    protected function executeRequestGroup(string $publicKey, array $products): Response
    {
        if ($this->applyProductFilters) {
            $products = $this->filterOutInvalidProducts($products);
        }

        if (empty($products)) {
            throw new NoProductsToCheckInException();
        }

        return $this->getSendRequest()->execute($publicKey, $products);
    }

    /**
     * Retrieves a new SendRequest instance.
     *
     * @since 1.0
     *
     * @return SendRequest
     */
    protected function getSendRequest(): SendRequest
    {
        return new SendRequest();
    }

    /**
     * Filters out any products that are currently locked.
     *
     * @since 1.0
     *
     * @param  array  $products
     *
     * @return array
     */
    protected function filterOutInvalidProducts(array $products): array
    {
        if (empty($this->lockedProductIds)) {
            return $products;
        }

        return array_values(array_filter($products, function (Product $product) {
            return ! in_array($product->productId, $this->lockedProductIds, true);
        }));
    }

    /**
     * @throws ServiceUnavailableException
     */
    protected function handleGroupResponse(Response $response, array $products)
    {
        $this->log("Response code: {$response->responseCode}");
        $this->log("Response body: {$response->responseBody}");

        if ($response->serviceIsUnavailable()) {
            throw new ServiceUnavailableException();
        }

        if (! $response->isOk() || $response->hasErrors()) {
            $this->log('Handling errors.');
            $this->responseErrorHandler->execute($response, $products);
        }
    }

    /**
     * Reschedules the next check-in for a later date.
     *
     * @since 1.0
     */
    protected function handleServiceUnavailable(): void
    {
        UpdateCheckInSchedule::make()->setNextCheckIn(UpdateCheckInSchedule::SERVICE_UNAVAILABLE);
    }
}
