<?php
/**
 * LoggableTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Traits;

use ContextWP\Contracts\LogInterface;
use ContextWP\Tests\TestCase;
use ContextWP\Traits\Loggable;
use Generator;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;

class LoggableTest extends TestCase
{
    /**
     * @covers \ContextWP\Traits\Loggable::setLogger()
     */
    public function testCanSetLogger(): void
    {
        /** @var Loggable&MockObject $loggable */
        $loggable = $this->getMockForTrait(Loggable::class);

        $this->assertNull($this->getInaccessibleProperty($loggable, 'logger')->getValue($loggable));

        $loggable->setLogger(Mockery::mock(LogInterface::class));

        $this->assertInstanceOf(
            LogInterface::class,
            $this->getInaccessibleProperty($loggable, 'logger')->getValue($loggable)
        );
    }

    /**
     * @covers       \ContextWP\Traits\Loggable::log()
     * @dataProvider providerCanLog
     */
    public function testCanLog(bool $hasLogger, bool $logIsExpected): void
    {
        /** @var Loggable&MockObject $loggable */
        $loggable     = $this->getMockForTrait(Loggable::class);
        $logInterface = Mockery::mock(LogInterface::class);
        $logInterface->expects('log')
            ->times($logIsExpected ? 1 : 0)
            ->with('Log message');

        if ($hasLogger) {
            $loggable->setLogger($logInterface);
        }

        $loggable->log('Log message');

        $this->assertConditionsMet();
    }

    /** @see testCanLog */
    public function providerCanLog(): Generator
    {
        yield 'has logger' => [true, true];
        yield 'no logger' => [false, false];
    }
}
