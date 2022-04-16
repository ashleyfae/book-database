<?php
/**
 * SendCheckInsTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Actions;

use ContextWP\Actions\HandleResponseErrors;
use ContextWP\Actions\SendCheckIns;
use ContextWP\Actions\SendRequest;
use ContextWP\Exceptions\NoProductsToCheckInException;
use ContextWP\Exceptions\ServiceUnavailableException;
use ContextWP\Http\Response;
use ContextWP\Registries\ProductRegistry;
use ContextWP\Repositories\ProductErrorsRepository;
use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\Product;
use Generator;
use Mockery;
use ReflectionException;

class SendCheckInsTest extends TestCase
{
    /**
     * @covers       \ContextWP\Actions\SendCheckIns::execute()
     * @dataProvider providerCanExecute
     */
    public function testCanExecute(bool $serviceIsAvailable, bool $shouldApplyFilters): void
    {
        $sendCheckIns = $this->createPartialMock(
            SendCheckIns::class,
            ['handleGroupResponse', 'executeRequestGroup', 'handleServiceUnavailable']
        );

        $repository = Mockery::mock(ProductErrorsRepository::class);
        $repository->expects('deleteExpiredErrors')
            ->once()
            ->andReturnNull();
        $repository->expects('getLockedProductIds')
            ->times($shouldApplyFilters ? 1 : 0)
            ->andReturn([]);

        $productRegistry = Mockery::mock(ProductRegistry::class);
        $productRegistry->expects('getProducts')
            ->once()
            ->andReturn(['pk' => ['product']]);

        $response = Mockery::mock(Response::class);

        if ($serviceIsAvailable) {
            $sendCheckIns->expects($this->once())
                ->method('executeRequestGroup')
                ->with('pk', ['product'])
                ->willReturn($response);
        } else {
            $sendCheckIns->expects($this->once())
                ->method('executeRequestGroup')
                ->with('pk', ['product'])
                ->willThrowException(new ServiceUnavailableException);
        }

        $sendCheckIns->expects($serviceIsAvailable ? $this->once() : $this->never())
            ->method('handleGroupResponse')
            ->with($response, ['product'])
            ->willReturn(null);

        $sendCheckIns->expects($serviceIsAvailable ? $this->never() : $this->once())
            ->method('handleServiceUnavailable');

        $this->setInaccessibleProperty($sendCheckIns, 'productErrorsRepository', $repository);
        $this->setInaccessibleProperty($sendCheckIns, 'applyProductFilters', $shouldApplyFilters);
        $this->setInaccessibleProperty($sendCheckIns, 'productRegistry', $productRegistry);

        $sendCheckIns->execute();

        $this->assertConditionsMet();
    }

    /** @see testCanExecute */
    public function providerCanExecute(): Generator
    {
        yield 'service available, apply filters' => [true, true];
        yield 'service available, no filters' => [true, false];
        yield 'service unavailable' => [false, true];
    }

    /**
     * @covers       \ContextWP\Actions\SendCheckIns::executeRequestGroup()
     * @dataProvider providerCanExecuteRequestGroup
     * @throws ReflectionException
     */
    public function testCanExecuteRequestGroup(
        bool $shouldApplyFilters,
        array $products,
        ?string $expectedException = null
    ): void {
        $sendCheckIns = $this->createPartialMock(
            SendCheckIns::class,
            ['filterOutInvalidProducts', 'getSendRequest']
        );

        $this->setInaccessibleProperty($sendCheckIns, 'applyProductFilters', $shouldApplyFilters);

        $sendCheckIns->expects($shouldApplyFilters ? $this->once() : $this->never())
            ->method('filterOutInvalidProducts')
            ->with($products)
            ->willReturnArgument(0);

        $response = Mockery::mock(Response::class);

        $request = Mockery::mock(SendRequest::class);
        $request->expects('execute')
            ->times(! empty($products) ? 1 : 0)
            ->with('public-key', $products)
            ->andReturn($response);

        $sendCheckIns->expects(! empty($products) ? $this->once() : $this->never())
            ->method('getSendRequest')
            ->willReturn($request);

        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $this->assertSame(
            $response,
            $this->invokeInaccessibleMethod($sendCheckIns, 'executeRequestGroup', 'public-key', $products)
        );
    }

    /** @see testCanExecuteRequestGroup */
    public function providerCanExecuteRequestGroup(): Generator
    {
        yield 'products with filters' => [
            'shouldApplyFilters' => true,
            'products'           => ['product1'],
            'expectedException'  => null,
        ];

        yield 'products without filters' => [
            'shouldApplyFilters' => false,
            'products'           => ['product1'],
            'expectedException'  => null,
        ];

        yield 'no products' => [
            'shouldApplyFilters' => true,
            'products'           => [],
            'expectedException'  => NoProductsToCheckInException::class,
        ];
    }

    /**
     * @covers       \ContextWP\Actions\SendCheckIns::filterOutInvalidProducts()
     * @dataProvider providerCanFilterOutInvalidProducts
     * @throws ReflectionException
     */
    public function testCanFilterOutInvalidProducts(array $products, array $lockedIds, array $expected): void
    {
        $sendCheckIns = new SendCheckIns();
        $this->setInaccessibleProperty($sendCheckIns, 'lockedProductIds', $lockedIds);

        $this->assertSame(
            $expected,
            $this->invokeInaccessibleMethod($sendCheckIns, 'filterOutInvalidProducts', $products)
        );
    }

    /** @see testCanFilterOutInvalidProducts */
    public function providerCanFilterOutInvalidProducts(): Generator
    {
        $product1 = new Product('pk-1', 'pid-1');
        $product2 = new Product('pk-2', 'pid-2');

        yield 'no locked products' => [
            'products'  => [$product1, $product2],
            'lockedIds' => [],
            'expected'  => [$product1, $product2],
        ];

        yield 'locked products but not in provided list' => [
            'products'  => [$product1, $product2],
            'lockedIds' => ['not-in-list', 'not-in-list-2'],
            'expected'  => [$product1, $product2],
        ];

        yield 'one product is locked' => [
            'products'  => [$product1, $product2],
            'lockedIds' => ['pid-1'],
            'expected'  => [$product2],
        ];

        yield 'all products are locked' => [
            'products'  => [$product1, $product2],
            'lockedIds' => ['pid-1', 'pid-2'],
            'expected'  => [],
        ];
    }

    /**
     * @covers       \ContextWP\Actions\SendCheckIns::handleGroupResponse()
     * @dataProvider providerCanHandleGroupResponse
     * @throws ReflectionException
     */
    public function testCanHandleGroupResponse(
        bool $isServiceAvailable,
        bool $isResponseOk,
        bool $responseHasErrors,
        bool $errorExecutionExpected,
        ?string $expectedException
    ): void {
        if (! empty($expectedException)) {
            $this->expectException($expectedException);
        }

        $response = Mockery::mock(Response::class);
        $response->expects('serviceIsUnavailable')
            ->once()
            ->andReturn(! $isServiceAvailable);

        $response->expects('isOk')
            ->times($isServiceAvailable ? 1 : 0)
            ->andReturn($isResponseOk);

        $response->expects('hasErrors')
            ->times($isServiceAvailable && $isResponseOk ? 1 : 0)
            ->andReturn($responseHasErrors);

        $responseHandler = Mockery::mock(HandleResponseErrors::class);
        $responseHandler->expects('execute')
            ->times($errorExecutionExpected ? 1 : 0)
            ->with($response, ['products'])
            ->andReturnNull();

        $sendCheckIns = new SendCheckIns();
        $this->setInaccessibleProperty($sendCheckIns, 'responseErrorHandler', $responseHandler);

        $this->invokeInaccessibleMethod($sendCheckIns, 'handleGroupResponse', $response, ['products']);

        $this->assertConditionsMet();
    }

    /** @see testCanHandleGroupResponse */
    public function providerCanHandleGroupResponse(): Generator
    {
        yield 'service unavailable' => [
            'isServiceAvailable'     => false,
            'isResponseOk'           => false,
            'responseHasErrors'      => true,
            'errorExecutionExpected' => false,
            'expectedException'      => ServiceUnavailableException::class,
        ];

        yield 'service available, response ok, no errors' => [
            'isServiceAvailable'     => true,
            'isResponseOk'           => true,
            'responseHasErrors'      => false,
            'errorExecutionExpected' => false,
            'expectedException'      => null,
        ];

        yield 'service available, response ok, with errors' => [
            'isServiceAvailable'     => true,
            'isResponseOk'           => true,
            'responseHasErrors'      => true,
            'errorExecutionExpected' => true,
            'expectedException'      => null,
        ];

        yield 'service available, response not ok, with errors' => [
            'isServiceAvailable'     => true,
            'isResponseOk'           => false,
            'responseHasErrors'      => true,
            'errorExecutionExpected' => true,
            'expectedException'      => null,
        ];

        yield 'service available, response not ok, no errors' => [
            'isServiceAvailable'     => true,
            'isResponseOk'           => false,
            'responseHasErrors'      => false,
            'errorExecutionExpected' => true,
            'expectedException'      => null,
        ];
    }
}
