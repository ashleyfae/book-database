<?php
/**
 * ErrorConsequenceTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace ContextWP\Tests\Unit\ValueObjects;

use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\ErrorConsequence;
use Generator;

class ErrorConsequenceTest extends TestCase
{
    /**
     * @covers \ContextWP\ValueObjects\ErrorConsequence::__construct()
     */
    public function testCanConstruct(): void
    {
        $consequence = new ErrorConsequence('pid', 'reason', 'body');

        $this->assertSame(
            'pid',
            $this->getInaccessibleProperty($consequence, 'productId')->getValue($consequence)
        );

        $this->assertSame(
            'reason',
            $this->getInaccessibleProperty($consequence, 'reason')->getValue($consequence)
        );

        $this->assertSame(
            'body',
            $this->getInaccessibleProperty($consequence, 'responseBody')->getValue($consequence)
        );
    }

    /**
     * @covers       \ContextWP\ValueObjects\ErrorConsequence::isPermanentlyLocked()
     * @dataProvider providerIsPermanentlyLocked
     */
    public function testIsPermanentlyLocked(string $reason, bool $expected): void
    {
        $consequence = new ErrorConsequence('pid', $reason);

        $this->assertSame($expected, $consequence->isPermanentlyLocked());
    }

    /** @see testIsPermanentlyLocked */
    public function providerIsPermanentlyLocked(): Generator
    {
        yield 'product not found' => ['product_not_found', true];
        yield 'validation error' => ['validation_error', false];
        yield 'at subscription limit' => ['at_subscription_limit', false];
    }

    /**
     * @covers \ContextWP\ValueObjects\ErrorConsequence::getLockedUntil()
     * @dataProvider providerGetLockedUntil
     */
    public function testGetLockedUntil(string $reason, ?string $expected): void
    {
        $consequence = new ErrorConsequence('pid', $reason);

        $this->assertSame(
            is_null($expected) ? $expected : date('Y-m-d H:i:s', strtotime($expected)),
            $consequence->getLockedUntil()
        );
    }

    /** @see testGetLockedUntil */
    public function providerGetLockedUntil(): Generator
    {
        yield 'product not found' => ['product_not_found', null];
        yield 'no active subscription' => ['no_active_subscription', '+2 weeks'];
    }
}
