<?php
/**
 * ProductRegistryTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Registries;

use ContextWP\Exceptions\InvalidProductException;
use ContextWP\Registries\ProductRegistry;
use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\Product;
use ReflectionException;

class ProductRegistryTest extends TestCase
{
    /**
     * @covers       \ContextWP\Registries\ProductRegistry::add()
     * @dataProvider providerCanAddProduct
     * @throws InvalidProductException
     */
    public function testAddProduct(?string $expectedException): void
    {
        $registry = $this->createPartialMock(ProductRegistry::class, ['validateProduct']);

        $product = new Product('public-key', 'my-product');

        if ($expectedException) {
            $registry->expects($this->once())
                ->method('validateProduct')
                ->with($product)
                ->willThrowException(new $expectedException);
        } else {
            $registry->expects($this->once())
                ->method('validateProduct')
                ->with($product)
                ->willReturn(null);
        }

        if (! empty($expectedException)) {
            $this->expectException($expectedException);
        }

        $registry->add($product);

        $this->assertSame(
            ['public-key' => [$product]],
            $registry->getProducts()
        );
    }

    public function providerCanAddProduct(): \Generator
    {
        yield 'valid product' => [null];
        yield 'fails validation' => [InvalidProductException::class];
    }

    /**
     * @covers \ContextWP\Registries\ProductRegistry::getProducts()
     * @throws ReflectionException
     */
    public function testGetProducts(): void
    {
        $registry = new ProductRegistry();

        $products = [
            'public-key' => [new Product('public-key', 'my-product')]
        ];

        $this->setInaccessibleProperty($registry, 'products', $products);

        $this->assertSame($products, $registry->getProducts());
    }
}
