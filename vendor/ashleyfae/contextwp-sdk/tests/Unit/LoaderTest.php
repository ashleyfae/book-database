<?php
/**
 * LoaderTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit;

use ContextWP\Loader;
use ContextWP\Tests\TestCase;
use Generator;

class LoaderTest extends TestCase
{
    /**
     * @covers       \ContextWP\Loader::isLaterVersion()
     * @dataProvider providerIsLaterVersion
     *
     * @param  array  $sdk
     * @param  bool  $isLater
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testIsLaterVersion(array $sdk, bool $isLater): void
    {
        $loader = Loader::instance();
        $this->setInaccessibleProperty($loader, 'latestSdk', ['version' => '2.0']);

        $this->assertSame(
            $isLater,
            $this->invokeInaccessibleMethod($loader, 'isLaterVersion', $sdk)
        );
    }

    /** @see testIsLaterVersion */
    public function providerIsLaterVersion(): Generator
    {
        yield 'New version is later' => [
            'sdk'     => ['version' => '2.2', 'path' => '/path/to/sdk'],
            'isLater' => true,
        ];

        yield 'New version is not later' => [
            'sdk'     => ['version' => '1.2', 'path' => '/path/to/sdk'],
            'isLater' => false,
        ];

        yield 'Same version is not later' => [
            'sdk'     => ['version' => '2.0', 'path' => '/path/to/sdk'],
            'isLater' => false,
        ];

        yield 'Invalid version is not later' => [
            'sdk'     => [],
            'isLater' => false,
        ];
    }

    /**
     * @covers \ContextWP\Loader::registerSdk()
     */
    public function testRegisterSdk(): void
    {
        Loader::instance()->registerSdk('2.0', '/path/to/sdk');

        $this->assertSame(
            [
                [
                    'version' => '2.0',
                    'path'    => '/path/to/sdk',
                ]
            ],
            $this->getInaccessibleProperty(Loader::instance(), 'registeredSdks')->getValue(Loader::instance())
        );
    }
}
