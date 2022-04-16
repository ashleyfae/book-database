<?php
/**
 * ProductRegistry.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Registries;

use ContextWP\Exceptions\InvalidProductException;
use ContextWP\Helpers\Str;
use ContextWP\Traits\Singleton;
use ContextWP\ValueObjects\Product;

/**
 * Holds all products that have been registered.
 *
 * @since 1.0
 */
class ProductRegistry
{
    use Singleton;

    /** @var array All products, grouped by public key */
    protected $products = [];

    /**
     * Adds a new product.
     *
     * @since 1.0
     *
     * @param  Product  $product
     *
     * @return $this
     * @throws InvalidProductException
     */
    public function add(Product $product): ProductRegistry
    {
        $this->validateProduct($product);

        if (! array_key_exists($product->publicKey, $this->products)) {
            $this->products[$product->publicKey] = [];
        }

        $this->products[$product->publicKey][] = $product;

        return $this;
    }

    /**
     * Ensures basic product requirements are met.
     *
     * @since 1.0
     *
     * @throws InvalidProductException
     */
    protected function validateProduct(Product $product): void
    {
        if (! Str::isUuid($product->productId)) {
            throw new InvalidProductException('Invalid product ID.');
        }

        if (! Str::isUuid($product->publicKey)) {
            throw new InvalidProductException('Invalid public key.');
        }
    }

    /**
     * Retrieves all products.
     *
     * @since 1.0
     *
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
