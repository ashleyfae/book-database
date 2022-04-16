<?php
/**
 * CheckInScheduleRepositoryTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Repositories;

use ContextWP\Repositories\CheckInScheduleRepository;
use ContextWP\Tests\TestCase;
use Generator;
use WP_Mock;

class CheckInScheduleRepositoryTest extends TestCase
{
    /**
     * @covers \ContextWP\Repositories\CheckInScheduleRepository::set()
     */
    public function testUpdate(): void
    {
        WP_Mock::userFunction('update_option')
            ->with('contextwp_next_checkin', 123, false)
            ->andReturnNull();

        (new CheckInScheduleRepository())->set(123);

        $this->assertConditionsMet();
    }

    /**
     * @covers       \ContextWP\Repositories\CheckInScheduleRepository::get()
     * @dataProvider providerCanGet
     */
    public function testCanGet($optionValue, ?int $expected): void
    {
        WP_Mock::userFunction('get_option')
            ->with('contextwp_next_checkin')
            ->andReturn($optionValue);

        $this->assertSame(
            $expected,
            (new CheckInScheduleRepository())->get()
        );
    }

    /** @see testCanGet */
    public function providerCanGet(): Generator
    {
        yield 'string value' => ['123', 123];
        yield 'integer value' => [456, 456];
        yield 'false value' => [false, null];
        yield 'empty string' => ['', null];
    }
}
