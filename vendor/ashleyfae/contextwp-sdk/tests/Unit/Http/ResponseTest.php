<?php
/**
 * ResponseTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Http;

use ContextWP\Http\Response;
use ContextWP\Tests\TestCase;
use Generator;
use ReflectionException;

class ResponseTest extends TestCase
{
    /**
     * @covers \ContextWP\Http\Response::__construct()
     */
    public function testCanConstruct(): void
    {
        $response = new Response(201, 'body');

        $this->assertSame(201, $response->responseCode);
        $this->assertSame('body', $response->responseBody);
    }

    /**
     * @covers \ContextWP\Http\Response::makeFromWpError()
     */
    public function testCanMakeFromWpError(): void
    {
        $error = \Mockery::mock('WP_Error');
        $error->expects('get_error_message')
            ->once()
            ->andReturn('error message');

        $response = Response::makeFromWpError($error);

        $this->assertSame(503, $response->responseCode);
        $this->assertSame('error message', $response->responseBody);
    }

    /**
     * @covers       \ContextWP\Http\Response::isOk()
     * @dataProvider providerIsOk
     */
    public function testIsOk(int $responseCode, bool $expected): void
    {
        $response = new Response($responseCode);

        $this->assertSame($expected, $response->isOk());
    }

    /** @see testIsOk */
    public function providerIsOk(): Generator
    {
        yield '200 is ok' => [200, true];
        yield '201 is ok' => [201, true];
        yield '204 is ok' => [204, true];
        yield '301 not ok' => [301, false];
        yield '422 not ok' => [422, false];
        yield '500 not ok' => [500, false];
        yield '503 not ok' => [503, false];
    }

    /**
     * @covers \ContextWP\Http\Response::hasErrors()
     * @dataProvider providerHasErrors
     */
    public function testHasErrors(array $body, bool $expected): void
    {
        $response = new Response(200, json_encode($body));

        $this->assertSame($expected, $response->hasErrors());
    }

    /** @see testHasErrors */
    public function providerHasErrors(): Generator
    {
        yield 'empty body' => [[], false];
        yield 'has error code' => [['error_code' => 'error'], true];
        yield 'has errors' => [['errors' => 'error'], true];
        yield 'has empty rejected' => [['rejected' => []], false];
        yield 'has rejected' => [['rejected' => 'value'], true];
    }

    /**
     * @covers       \ContextWP\Http\Response::serviceIsUnavailable()
     * @dataProvider providerServiceIsUnavailable
     */
    public function testServiceIsUnavailable(int $responseCode, bool $expected): void
    {
        $this->assertSame(
            (new Response($responseCode))->serviceIsUnavailable(),
            $expected
        );
    }

    /** @see testServiceIsUnavailable */
    public function providerServiceIsUnavailable(): Generator
    {
        yield '500 is unavailable' => [500, true];
        yield '503 is unavailable' => [503, true];
        yield '400 is available' => [400, false];
        yield '200 is available' => [200, false];
        yield '600 is available' => [600, false];
    }

    /**
     * @covers       \ContextWP\Http\Response::getJson()
     * @dataProvider providerCanGetJson
     * @throws ReflectionException
     */
    public function testCanGetJson(?string $body, ?array $expected): void
    {
        $response = new Response(200, $body);

        $this->assertSame(
            $expected,
            $this->invokeInaccessibleMethod($response, 'getJson')
        );
    }

    /** @see testCanGetJson */
    public function providerCanGetJson(): Generator
    {
        yield 'null body' => [null, []];
        yield 'empty body' => ['', []];
        yield 'body that can be decoded' => [
            '{"error_code":"missing_auth_header","error_message":"Missing authentication header."}',
            ['error_code' => 'missing_auth_header', 'error_message' => 'Missing authentication header.'],
        ];
        yield 'body that cannot be decoded' => ['body', []];
    }

    /**
     * @covers \ContextWP\Http\Response::jsonKey()
     * @dataProvider providerCanGetJsonKey
     */
    public function testCanGetJsonKey(string $key, $default, $expected): void
    {
        $response = new Response(200,
            '{"error_code":"missing_auth_header","error_message":"Missing authentication header."}');

        $this->assertSame(
            $expected,
            $response->jsonKey($key, $default)
        );
    }

    /** @see testCanGetJsonKey */
    public function providerCanGetJsonKey(): Generator
    {
        yield 'key not set' => [
            'key'      => 'not-set',
            'default'  => 'default',
            'expected' => 'default',
        ];

        yield 'key is set' => [
            'key'      => 'error_code',
            'default'  => '123',
            'expected' => 'missing_auth_header',
        ];
    }
}
