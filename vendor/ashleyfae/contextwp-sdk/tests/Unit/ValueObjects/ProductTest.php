<?php
/**
 * ProductTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\ValueObjects;

use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\Product;
use Generator;

class ProductTest extends TestCase
{
    /**
     * @covers \ContextWP\ValueObjects\Product::__construct()
     */
    public function testCanConstruct()
    {
        $product = new Product('public-key', 'my-product');

        $this->assertSame(
            'public-key',
            $this->getInaccessibleProperty($product, 'publicKey')->getValue($product)
        );

        $this->assertSame(
            'my-product',
            $this->getInaccessibleProperty($product, 'productId')->getValue($product)
        );
    }

    /**
     * @covers \ContextWP\ValueObjects\Product::setVersion()
     */
    public function testCanSetVersion(): void
    {
        $product = new Product('pk', 'pid');

        $this->assertNull($this->getInaccessibleProperty($product, 'version')->getValue($product));

        $product->setVersion('2.5');

        $this->assertSame(
            '2.5',
            $this->getInaccessibleProperty($product, 'version')->getValue($product)
        );
    }

    /**
     * @covers       \ContextWP\ValueObjects\Product::toArray()
     * @dataProvider providerToArray
     */
    public function testToArray(?string $version, array $expected): void
    {
        $product = new Product('pk', 'pid');

        if (! empty($version)) {
            $product->setVersion($version);
        }

        $this->assertSame($expected, $product->toArray());
    }

    /** @see testToArray */
    public function providerToArray(): Generator
    {
        yield 'no version' => [
            'version'  => null,
            'expected' => [
                'product_id'      => 'pid',
                'product_version' => null,
            ],
        ];

        yield 'has version' => [
            'version'  => '5.0',
            'expected' => [
                'product_id'      => 'pid',
                'product_version' => '5.0',
            ],
        ];
    }
}
