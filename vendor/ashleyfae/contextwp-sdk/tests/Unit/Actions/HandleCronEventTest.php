<?php
/**
 * HandleCronEventTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Actions;

use ContextWP\Actions\HandleCronEvent;
use ContextWP\Actions\SendCheckIns;
use ContextWP\Repositories\CheckInScheduleRepository;
use ContextWP\Tests\TestCase;
use Exception;
use Generator;
use Mockery;
use WP_Mock;

class HandleCronEventTest extends TestCase
{
    /**
     * @covers \ContextWP\Actions\HandleCronEvent::__construct()
     */
    public function testCanConstruct(): void
    {
        $handler = new HandleCronEvent();

        $this->assertInstanceOf(
            CheckInScheduleRepository::class,
            $this->getInaccessibleProperty($handler, 'checkInRepository')->getValue($handler)
        );

        $this->assertInstanceOf(
            SendCheckIns::class,
            $this->getInaccessibleProperty($handler, 'sendCheckIns')->getValue($handler)
        );
    }

    /**
     * @covers \ContextWP\Actions\HandleCronEvent::load()
     */
    public function testCanInit(): void
    {
        $handler = new HandleCronEvent();

        WP_Mock::expectActionAdded(
            'wp',
            [$handler, 'maybeScheduleEvent']
        );

        WP_Mock::expectActionAdded(
            'contextwp_checkin',
            [$handler, 'handleEvent']
        );

        $handler->load();

        $this->assertConditionsMet();
    }

    /**
     * @covers       \ContextWP\Actions\HandleCronEvent::maybeScheduleEvent()
     * @dataProvider providerMaybeScheduleEvent
     */
    public function testMaybeScheduleEvent(bool $hasScheduled): void
    {
        $handler = new HandleCronEvent();

        WP_Mock::userFunction('wp_next_scheduled')
            ->once()
            ->with('contextwp_checkin')
            ->andReturn($hasScheduled);

        WP_Mock::userFunction('wp_schedule_event')
            ->times($hasScheduled ? 0 : 1)
            ->with(time(), 'daily', 'contextwp_checkin')
            ->andReturnNull();

        $handler->maybeScheduleEvent();

        $this->assertConditionsMet();
    }

    /** @see testMaybeScheduleEvent */
    public function providerMaybeScheduleEvent(): Generator
    {
        yield 'has scheduled' => [true];
        yield 'has not scheduled' => [false];
    }

    /**
     * @covers       \ContextWP\Actions\HandleCronEvent::needsCheckIn()
     * @dataProvider providerNeedsCheckIn
     */
    public function testNeedsCheckIn(?int $repoValue, bool $expected): void
    {
        $repository = Mockery::mock(CheckInScheduleRepository::class);
        $repository->expects('get')
            ->once()
            ->andReturn($repoValue);

        $handler = new HandleCronEvent();
        $this->setInaccessibleProperty($handler, 'checkInRepository', $repository);

        $this->assertSame(
            $expected,
            $this->invokeInaccessibleMethod($handler, 'needsCheckIn')
        );
    }

    /** @see testNeedsCheckIn */
    public function providerNeedsCheckIn(): Generator
    {
        yield 'empty timestamp needs check-in' => [null, true];
        yield 'timestamp in past needs check-in' => [strtotime('-1 day'), true];
        yield 'timestamp in future doesn\'t need check-in' => [strtotime('+1 day'), false];
    }

    /**
     * @covers       \ContextWP\Actions\HandleCronEvent::handleCheckIn()
     * @dataProvider providerCanHandleCheckIn
     */
    public function testCanHandleCheckIn(
        ?Exception $exceptionThrown,
        bool $isLoggingEnabled,
        bool $logIsExpected
    ): void {
        $sendCheckIns = Mockery::mock(SendCheckIns::class);
        if (! empty($exceptionThrown)) {
            $sendCheckIns->expects('execute')
                ->once()
                ->andThrow($exceptionThrown);
        } else {
            $sendCheckIns->expects('execute')
                ->once()
                ->andReturnNull();
        }

        $handler = $this->createPartialMock(HandleCronEvent::class, ['shouldLogExceptions', 'logException']);
        $handler->expects(! empty($exceptionThrown) ? $this->once() : $this->never())
            ->method('shouldLogExceptions')
            ->willReturn($isLoggingEnabled);
        $handler->expects($logIsExpected ? $this->once() : $this->never())
            ->method('logException')
            ->with($exceptionThrown)
            ->willReturn(null);

        $this->setInaccessibleProperty($handler, 'sendCheckIns', $sendCheckIns);

        $this->invokeInaccessibleMethod($handler, 'handleCheckIn');

        $this->assertConditionsMet();
    }

    /** @see testCanHandleCheckIn */
    public function providerCanHandleCheckIn(): Generator
    {
        yield 'no errors, logging disabled' => [null, false, false];
        yield 'no errors, logging enabled' => [null, true, false];
        yield 'exception, logging disabled' => [new Exception, false, false];
        yield 'exception, logging enabled' => [new Exception, true, true];
    }
}
