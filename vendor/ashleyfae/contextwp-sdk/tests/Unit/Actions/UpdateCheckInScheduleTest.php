<?php
/**
 * UpdateCheckInScheduleTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Actions;

use ContextWP\Actions\UpdateCheckInSchedule;
use ContextWP\Repositories\CheckInScheduleRepository;
use ContextWP\Tests\TestCase;
use Generator;
use Mockery;

class UpdateCheckInScheduleTest extends TestCase
{
    /**
     * @covers \ContextWP\Actions\UpdateCheckInSchedule::__construct()
     */
    public function testCanConstruct(): void
    {
        $schedule = new UpdateCheckInSchedule();

        $this->assertInstanceOf(
            CheckInScheduleRepository::class,
            $this->getInaccessibleProperty($schedule, 'checkInRepository')->getValue($schedule)
        );
    }

    /**
     * @covers       \ContextWP\Actions\UpdateCheckInSchedule::setNextCheckIn()
     * @dataProvider providerCanSetNextCheckIn
     */
    public function testCanSetNextCheckIn(?string $providedPeriod, string $expectedPeriod): void
    {
        $schedule = new UpdateCheckInSchedule();

        $repository = Mockery::mock(CheckInScheduleRepository::class);
        $repository->expects('set')
            ->once()
            ->with(strtotime($expectedPeriod))
            ->andReturnNull();

        $this->setInaccessibleProperty($schedule, 'checkInRepository', $repository);

        $schedule->setNextCheckIn($providedPeriod);

        $this->assertConditionsMet();
    }

    /** @see testCanSetNextCheckIn */
    public function providerCanSetNextCheckIn(): Generator
    {
        yield 'null period uses regular interval' => [null, '+1 week'];
        yield 'empty period uses regular period' => ['', '+1 week'];
        yield 'valid period supplied' => ['+3 days', '+3 days'];
        yield 'period that fails strtotime uses regular' => ['this-isnt-valid', '+1 week'];
    }
}
