<?php
/**
 * RequestTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Http;

use ContextWP\Exceptions\MissingPublicKeyException;
use ContextWP\Http\Request;
use ContextWP\Http\Response;
use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\Environment;
use ContextWP\ValueObjects\Product;
use Exception;
use Generator;
use Mockery;
use ReflectionException;
use WP_Mock;

class RequestTest extends TestCase
{
    /**
     * @covers \ContextWP\Http\Request::setUrl()
     * @throws ReflectionException
     */
    public function testCanSetUrl(): void
    {
        $request = new Request();

        $this->assertNull($this->getInaccessibleProperty($request, 'url')->getValue($request));

        $request->setUrl('contextwp.com');

        $this->assertSame(
            'contextwp.com',
            $this->getInaccessibleProperty($request, 'url')->getValue($request)
        );
    }

    /**
     * @covers \ContextWP\Http\Request::setPublicKey()
     * @throws ReflectionException
     */
    public function testCanSetPublicKey(): void
    {
        $request = new Request();

        $this->assertNull($this->getInaccessibleProperty($request, 'publicKey')->getValue($request));

        $request->setPublicKey('public-key');

        $this->assertSame(
            'public-key',
            $this->getInaccessibleProperty($request, 'publicKey')->getValue($request)
        );
    }

    /**
     * @covers \ContextWP\Http\Request::setEnvironment()
     * @throws ReflectionException
     */
    public function setEnvironment(): void
    {
        $request     = new Request();
        $environment = new Environment();

        $this->assertNull($this->getInaccessibleProperty($request, 'environment')->getValue($request));

        $request->setEnvironment($environment);

        $this->assertSame(
            $environment,
            $this->getInaccessibleProperty($request, 'environment')->getValue($request)
        );
    }

    /**
     * @covers \ContextWP\Http\Request::setProducts()
     * @throws ReflectionException
     */
    public function setProducts(): void
    {
        $request  = new Request();
        $products = [new Product('public-key', 'test')];

        $this->assertNull($this->getInaccessibleProperty($request, 'products')->getValue($request));

        $request->setProducts($products);

        $this->assertSame(
            $products,
            $this->getInaccessibleProperty($request, 'products')->getValue($request)
        );
    }

    /**
     * @covers \ContextWP\Http\Request::execute()
     * @throws Exception
     */
    public function testCanExecute(): void
    {
        $request = $this->createPartialMock(Request::class, ['makeResponse', 'makeHeaders', 'makeBody']);
        $request->setUrl('example.com');

        $request->expects($this->once())
            ->method('makeHeaders')
            ->willReturn(['header']);

        $request->expects($this->once())
            ->method('makeBody')
            ->willReturn(['body']);

        WP_Mock::userFunction('wp_remote_post')
            ->times(1)
            ->with('example.com', [
                'headers' => ['header'],
                'body'    => json_encode(['body']),
            ])
            ->andReturn([]);

        $response = new Response(204, 'body');

        $request->expects($this->once())
            ->method('makeResponse')
            ->with([])
            ->willReturn($response);

        $this->assertSame($response, $request->execute());
    }

    /**
     * @covers       \ContextWP\Http\Request::makeHeaders()
     * @dataProvider providerCanMakePublicKey
     * @throws ReflectionException
     */
    public function testCanMakeHeaders(?string $publicKey, ?string $expectedException = null): void
    {
        $request = new Request();

        if (! is_null($publicKey)) {
            $this->setInaccessibleProperty($request, 'publicKey', $publicKey);
        }

        if (! empty($expectedException)) {
            $this->expectException($expectedException);
        }

        $this->assertSame(
            [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'Public-Key'   => $publicKey,
            ],
            $this->invokeInaccessibleMethod($request, 'makeHeaders')
        );
    }

    /** @see testCanMakeHeaders */
    public function providerCanMakePublicKey(): Generator
    {
        yield 'has public key' => ['public-key-123'];
        yield 'no public key' => [null, MissingPublicKeyException::class];
    }

    /**
     * @covers \ContextWP\Http\Request::makeBody()
     * @throws ReflectionException
     */
    public function testCanMakeBody(): void
    {
        $request = new Request();

        $environment = Mockery::mock(Environment::class);
        $environment->expects('toArray')->once()->andReturn(['environment']);
        $request->setEnvironment($environment);

        $product = Mockery::mock(Product::class);
        $product->expects('toArray')->once()->andReturn(['product']);
        $request->setProducts([$product]);

        $this->assertSame(
            [
                'environment' => ['environment'],
                'products'    => [['product']],
            ],
            $this->invokeInaccessibleMethod($request, 'makeBody')
        );
    }

    /**
     * @covers       \ContextWP\Http\Request::makeResponse()
     * @dataProvider providerCanMakeResponse
     * @throws ReflectionException
     */
    public function testCanMakeResponse($response, bool $isWpError, Response $expected): void
    {
        WP_Mock::userFunction('is_wp_error')
            ->times(1)
            ->with($response)
            ->andReturn($isWpError);

        WP_Mock::userFunction('wp_remote_retrieve_response_code')
            ->times($isWpError ? 0 : 1)
            ->with($response)
            ->andReturn($response['response']['code'] ?? '');

        WP_Mock::userFunction('wp_remote_retrieve_body')
            ->times($isWpError ? 0 : 1)
            ->with($response)
            ->andReturn($response['body'] ?? '');

        /** @var Response $actualResponse */
        $actualResponse = $this->invokeInaccessibleMethod(new Request, 'makeResponse', $response);

        $this->assertSame($expected->responseCode, $actualResponse->responseCode);
        $this->assertSame($expected->responseBody, $actualResponse->responseBody);
    }

    /** @see testCanMakeResponse */
    public function providerCanMakeResponse(): Generator
    {
        yield '204 response' => [
            'response'  => [
                'response' => ['code' => 204],
                'body'     => 'success',
            ],
            'isWpError' => false,
            'expected'  => new Response(204, 'success'),
        ];

        yield '400 response without body' => [
            'response'  => [
                'response' => ['code' => 400],
            ],
            'isWpError' => false,
            'expected'  => new Response(400, null),
        ];

        // @todo add WP Error test here
    }
}
