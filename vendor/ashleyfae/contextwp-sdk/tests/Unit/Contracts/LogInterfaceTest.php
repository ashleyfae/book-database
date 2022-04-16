<?php
/**
 * LogInterfaceTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Contracts;

use ContextWP\Contracts\LogInterface;
use ContextWP\Tests\TestCase;
use ContextWP\Traits\Loggable;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;

class LogInterfaceTest extends TestCase
{
    /**
     * @covers \ContextWP\Contracts\LogInterface::log()
     * @covers \ContextWP\Traits\Loggable::setLogger()
     * @covers \ContextWP\Traits\Loggable::log()
     */
    public function testCanLog(): void
    {
        $logInterface = Mockery::mock(LogInterface::class);
        $logInterface->expects('log')
            ->once()
            ->with('Log message');

        /** @var Loggable&MockObject $loggable */
        $loggable = $this->getMockForTrait(Loggable::class);
        $loggable->setLogger($logInterface);
        $loggable->log('Log message');

        $this->assertConditionsMet();
    }
}
