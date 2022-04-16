<?php
/**
 * Product.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\ValueObjects;

use ContextWP\Contracts\Arrayable;
use ContextWP\Helpers\Str;

/**
 * Represents a Product in ContextWP.
 */
class Product implements Arrayable
{
    /** @var string Customer public key */
    public $publicKey;

    /** @var string Product UUID */
    public $productId;

    /** @var null Product version */
    protected $version = null;

    /**
     * @param  string  $publicKey  Public key on the customer's account.
     * @param  string  $productId  Product UUID.
     */
    public function __construct(string $publicKey, string $productId)
    {
        $this->publicKey = $publicKey;
        $this->productId = $productId;
    }

    /**
     * Sets the product version.
     *
     * @since 1.0
     *
     * @param  string  $version
     *
     * @return $this
     */
    public function setVersion(string $version): Product
    {
        $this->version = Str::sanitize($version);

        return $this;
    }

    /**
     * Converts the product to what's expected in the API request.
     *
     * @since 1.0
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'product_id'      => Str::maxChars($this->productId, 100),
            'product_version' => is_string($this->version) ? Str::maxChars($this->version, 50) : null,
        ];
    }
}
