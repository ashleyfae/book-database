<?php
/**
 * HandleResponseErrorsTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Actions;

use ContextWP\Actions\HandleResponseErrors;
use ContextWP\Http\Response;
use ContextWP\Repositories\ProductErrorsRepository;
use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\ErrorConsequence;
use ContextWP\ValueObjects\Product;
use Generator;
use Mockery;
use ReflectionException;

class HandleResponseErrorsTest extends TestCase
{
    /**
     * @covers \ContextWP\Actions\HandleResponseErrors::execute()
     * @dataProvider providerCanExecute
     */
    public function testCanExecute(
        string $responseBody,
        bool $sameConsequenceForAll,
        ?string $expectedErrorCode
    ): void {
        $handler = $this->createPartialMock(
            HandleResponseErrors::class,
            ['addConsequenceCodeForAll', 'addIndividualProductConsequences']
        );

        $products = [new Product('public-key', 'pid')];
        $response = new Response(400, $responseBody);

        $handler->expects($sameConsequenceForAll ? $this->once() : $this->never())
            ->method('addConsequenceCodeForAll')
            ->with($expectedErrorCode, $products)
            ->willReturn(null);

        $handler->expects($sameConsequenceForAll ? $this->never() : $this->once())
            ->method('addIndividualProductConsequences')
            ->with($response->jsonKey('rejected'))
            ->willReturn(null);

        $handler->execute($response, $products);
    }

    /** @see testCanExecute */
    public function providerCanExecute(): Generator
    {
        yield 'missing auth header' => [
            '{"error_code":"missing_auth_header","error_message":"Missing authentication header."}',
            true,
            'missing_auth_header',
        ];

        yield 'validation error' => [
            '{"message":"The environment field is required. (and 2 more errors)","errors":{"environment":["The environment field is required."],"environment.source_hash":["The environment.source hash field is required."],"products":["The products field is required."]}}',
            true,
            'validation_error',
        ];

        yield 'individual product not found error' => [
            '{"accepted":[],"rejected":{"4f9c853d-1baf-4c2f-96cb-1f464ea3680f":"product_not_found"}}',
            false,
            'product_not_found',
        ];
    }

    /**
     * @covers \ContextWP\Actions\HandleResponseErrors::makeConsequence()
     * @throws ReflectionException
     */
    public function testMakeConsequence(): void
    {
        $handler = new HandleResponseErrors();
        $this->setInaccessibleProperty($handler, 'response', new Response(200, 'response-body'));

        /** @var ErrorConsequence $consequence */
        $consequence = $this->invokeInaccessibleMethod(
            $handler,
            'makeConsequence',
            'pid',
            'error_code'
        );

        $this->assertInstanceOf(ErrorConsequence::class, $consequence);
        $this->assertSame('pid', $consequence->productId);
        $this->assertSame('error_code', $consequence->reason);
        $this->assertSame('response-body', $consequence->responseBody);
    }

    protected function mockProductErrorsRepo(HandleResponseErrors $handler, array $expectedConsequences): void
    {
        $mockRepo = Mockery::mock(ProductErrorsRepository::class);
        $mockRepo->expects('lockProducts')
            ->once()
            ->with($expectedConsequences);

        $this->setInaccessibleProperty($handler, 'productErrorsRepository', $mockRepo);
    }

    /**
     * @covers \ContextWP\Actions\HandleResponseErrors::addConsequenceCodeForAll()
     */
    public function testCanAddConsequenceCodeForAll(): void
    {
        $handler = $this->createPartialMock(HandleResponseErrors::class, ['makeConsequence']);
        $product = new Product('pk', 'pid');

        $consequence = new ErrorConsequence('pid', 'error-code');
        $handler->expects($this->once())
            ->method('makeConsequence')
            ->with('pid', 'error-code')
            ->willReturn($consequence);

        $this->mockProductErrorsRepo($handler, [$consequence]);

        $this->invokeInaccessibleMethod($handler, 'addConsequenceCodeForAll', 'error-code', [$product]);

        $this->assertConditionsMet();
    }

    /**
     * @covers \ContextWP\Actions\HandleResponseErrors::addIndividualProductConsequences()
     */
    public function testCanAddProductConsequences(): void
    {
        $handler = $this->createPartialMock(HandleResponseErrors::class, ['makeConsequence']);

        $consequence = new ErrorConsequence('pid', 'error-code');
        $handler->expects($this->once())
            ->method('makeConsequence')
            ->with('pid', 'error-code')
            ->willReturn($consequence);

        $this->mockProductErrorsRepo($handler, [$consequence]);

        $this->invokeInaccessibleMethod($handler, 'addIndividualProductConsequences', ['pid' => 'error-code']);

        $this->assertConditionsMet();
    }
}
