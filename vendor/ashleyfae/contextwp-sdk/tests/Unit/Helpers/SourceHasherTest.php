<?php
/**
 * SourceHasherTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Helpers;

use ContextWP\Helpers\SourceHasher;
use ContextWP\Tests\TestCase;
use Generator;
use ReflectionException;
use WP_Mock;

class SourceHasherTest extends TestCase
{
    /**
     * @covers       \ContextWP\Helpers\SourceHasher::getHash()
     * @dataProvider providerCanGetHash
     *
     * @param  string|null  $storedHash
     * @param  bool  $expectsToCreateNew
     *
     * @return void
     */
    public function testCanGetHash(?string $storedHash, bool $expectsToCreateNew): void
    {
        $hasher = $this->createPartialMock(
            SourceHasher::class,
            ['getStoredHash', 'hash', 'normalizeSiteUrl', 'saveHash']
        );

        $hasher->expects($this->once())
            ->method('getStoredHash')
            ->willReturn($storedHash);

        $hasher->expects($expectsToCreateNew ? $this->once() : $this->never())
            ->method('hash')
            ->with('example.com')
            ->willReturn('hash');

        $hasher->expects($expectsToCreateNew ? $this->once() : $this->never())
            ->method('normalizeSiteUrl')
            ->willReturn('example.com');

        $hasher->expects($expectsToCreateNew ? $this->once() : $this->never())
            ->method('saveHash')
            ->with('hash')
            ->willReturn(null);

        $this->assertSame('hash', $hasher->getHash());
    }

    /** @see testCanGetHash */
    public function providerCanGetHash(): Generator
    {
        yield 'existing hash' => ['hash', false];
        yield 'existing is null, make new' => [null, true];
        yield 'existing is empty string, make new' => ['', true];
    }

    /**
     * @covers       \ContextWP\Helpers\SourceHasher::getStoredHash()
     * @dataProvider providerCanGetStoredHash
     */
    public function testCanGetStoredHash($getOptionValue, ?string $expected): void
    {
        WP_Mock::userFunction('get_option')
            ->with('contextwp_source_hash')
            ->andReturn($getOptionValue);

        $this->assertSame(
            $expected,
            $this->invokeInaccessibleMethod(new SourceHasher(), 'getStoredHash')
        );
    }

    /** @see testCanGetStoredHash */
    public function providerCanGetStoredHash(): Generator
    {
        yield 'option is false' => [
            'getOptionValue' => false,
            'expected'       => null,
        ];

        yield 'option is empty string' => [
            'getOptionValue' => '',
            'expected'       => null,
        ];

        yield 'option has value' => [
            'getOptionValue' => 'hash',
            'expected'       => 'hash',
        ];

        yield 'option has int value' => [
            'getOptionValue' => 123,
            'expected'       => '123',
        ];
    }

    /**
     * @covers \ContextWP\Helpers\SourceHasher::saveHash()
     * @throws ReflectionException
     */
    public function testCanSaveHash(): void
    {
        WP_Mock::userFunction('update_option')
            ->with('contextwp_source_hash', 'hash')
            ->andReturn(null);

        $this->invokeInaccessibleMethod(new SourceHasher(), 'saveHash', 'hash');

        $this->assertConditionsMet();
    }

    /**
     * @covers       \ContextWP\Helpers\SourceHasher::normalizeSiteUrl()
     * @dataProvider providerCanNormalizeSiteUrl
     */
    public function testCanNormalizeSiteUrl(string $raw, string $expected): void
    {
        $hasher = $this->createPartialMock(SourceHasher::class, ['getSiteUrl']);

        $hasher->expects($this->once())
            ->method('getSiteUrl')
            ->willReturn($raw);

        $this->assertSame(
            $expected,
            $this->invokeInaccessibleMethod($hasher, 'normalizeSiteUrl')
        );
    }

    /** @see testCanNormalizeSiteUrl */
    public function providerCanNormalizeSiteUrl(): Generator
    {
        yield 'uppercase' => [
            'HTTPS://WWW.MYSITE.COM',
            'www.mysite.com'
        ];

        yield 'trailing slash' => [
            'https://mysite.com/',
            'mysite.com',
        ];

        yield 'spaces before and after' => [
            ' http://mysite.com ',
            'mysite.com',
        ];

        yield 'url with path' => [
            'https://mysite.com/wordpress/',
            'mysite.com/wordpress',
        ];

        yield 'query string' => [
            'https://mysite.com?test=123',
            'mysite.com',
        ];
    }
}
