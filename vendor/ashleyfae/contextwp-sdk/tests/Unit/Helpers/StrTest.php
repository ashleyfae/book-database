<?php
/**
 * StrTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Helpers;

use ContextWP\Helpers\Str;
use ContextWP\Tests\TestCase;
use Generator;

class StrTest extends TestCase
{
    /**
     * @covers       \ContextWP\Helpers\Str::sanitize()
     * @dataProvider providerCanSanitize
     */
    public function testCanSanitize($input, string $expected): void
    {
        $this->assertSame($expected, Str::sanitize($input));
    }

    /** @see testCanSanitize */
    public function providerCanSanitize(): Generator
    {
        yield 'HTML tags stripped from string' => [
            'input'    => '<b>Test</b>',
            'expected' => 'Test',
        ];

        yield 'non-string converted to string' => [
            'input'    => 123,
            'expected' => '123',
        ];
    }

    /**
     * @covers       \ContextWP\Helpers\Str::isUuid()
     * @dataProvider providerIsUuid
     */
    public function testIsUuid(string $string, bool $expected): void
    {
        $this->assertSame($expected, Str::isUuid($string));
    }

    /** @see testIsUuid */
    public function providerIsUuid(): Generator
    {
        yield 'valid uuid' => ['4f9c853d-1baf-4c2f-96cb-1f464ea3680f', true];
        yield 'invalid uuid' => ['not-a-uuid', false];
        yield 'empty string' => ['', false];
    }

    /**
     * @covers       \ContextWP\Helpers\Str::maxChars()
     * @dataProvider providerMaxLength
     */
    public function testMaxLength(string $input, int $maxLength, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::maxChars($input, $maxLength)
        );
    }

    /** @see testMaxLength */
    public function providerMaxLength(): Generator
    {
        yield 'max length greater than input' => [
            'input'     => 'My string',
            'maxLength' => 100,
            'expected'  => 'My string',
        ];

        yield 'input longer than max length' => [
            'input'     => 'My string',
            'maxLength' => 2,
            'expected'  => 'My',
        ];
    }
}
