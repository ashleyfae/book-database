<?php
/**
 * ErrorConsequence.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\ValueObjects;

class ErrorConsequence
{
    const MissingAuthenticationHeader = 'missing_auth_header';
    const InvalidAuthenticationHeader = 'invalid_auth_header';
    const ProductNotFound = 'product_not_found';
    const NoActiveSubscription = 'no_active_subscription';
    const AtCheckInLimit = 'at_subscription_limit';
    const ValidationError = 'validation_error';

    /** @var string $productId ID of the product */
    public $productId;

    /** @var string $reason Reason for the error */
    public $reason;

    /** @var string|null $responseBody API response body */
    public $responseBody;

    /**
     * Constructor
     *
     * @since 1.0
     *
     * @param  string  $productId  ID of the product.
     * @param  string  $reason  Error reason from the API response.
     * @param  string|null  $responseBody  Full response body.
     */
    public function __construct(string $productId, string $reason, ?string $responseBody = null)
    {
        $this->productId    = $productId;
        $this->reason       = $reason;
        $this->responseBody = $responseBody;
    }

    /**
     * Determines if the product should be permanently locked. This is only used for errors that are
     * impossible to recover (without changing the product ID, at least).
     *
     * @since 1.0
     *
     * @return bool
     */
    public function isPermanentlyLocked(): bool
    {
        return in_array($this->reason, [
            static::ProductNotFound
        ], true);
    }

    public function getLockedUntil(): ?string
    {
        switch ($this->reason) {
            case static::ProductNotFound:
                return null;

            default :
                return date('Y-m-d H:i:s', strtotime('+2 weeks'));
        }
    }
}
