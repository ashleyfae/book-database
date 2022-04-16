<?php
/**
 * ProductErrorsRepositoryTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Repositories;

use Ashleyfae\WPDB\DB;
use ContextWP\Repositories\ProductErrorsRepository;
use ContextWP\Tests\TestCase;
use ContextWP\ValueObjects\ErrorConsequence;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ProductErrorsRepositoryTest extends TestCase
{
    /**
     * @covers \ContextWP\Repositories\ProductErrorsRepository::deleteExpiredErrors()
     *
     * @throws ReflectionException
     */
    public function testCanDeleteExpiredErrors(): void
    {
        $repository = $this->createPartialMock(ProductErrorsRepository::class, ['getNow', 'getTableName']);

        $repository->expects($this->once())
            ->method('getTableName')
            ->willReturn('wp_contextwp_table');

        $repository->expects($this->once())
            ->method('getNow')
            ->willReturn('2022-04-10 12:57:00');

        $this->mockStaticMethod(DB::class, 'prepare')
            ->once()
            ->with(
                "DELETE FROM wp_contextwp_table WHERE locked_until IS NOT NULL AND locked_until < %s",
                '2022-04-10 12:57:00'
            )
            ->andReturn("DELETE FROM wp_contextwp_table WHERE locked_until IS NOT NULL AND locked_until < '2022-04-10 12:57:00'");

        $this->mockStaticMethod(DB::class, '__callStatic')
            ->once()
            ->with('query', ["DELETE FROM wp_contextwp_table WHERE locked_until IS NOT NULL AND locked_until < '2022-04-10 12:57:00'"])
            ->andReturnNull();

        $repository->deleteExpiredErrors();
    }

    /**
     * @covers \ContextWP\Repositories\ProductErrorsRepository::getLockedProductIds()
     * @throws ReflectionException
     */
    public function testCanGetLockedProductIds(): void
    {
        $repository = $this->createPartialMock(ProductErrorsRepository::class, ['getNow', 'getTableName']);

        $repository->expects($this->once())
            ->method('getTableName')
            ->willReturn('wp_contextwp_table');

        $repository->expects($this->once())
            ->method('getNow')
            ->willReturn('2022-04-10 12:57:00');

        $this->mockStaticMethod(DB::class, 'prepare')
            ->once()
            ->with(
                "SELECT product_id FROM wp_contextwp_table WHERE permanently_locked = 0 AND locked_until IS NOT NULL AND locked_until <= %s",
                '2022-04-10 12:57:00'
            )
            ->andReturn("SELECT product_id FROM wp_contextwp_table WHERE permanently_locked = 0 AND locked_until IS NOT NULL AND locked_until <= '2022-04-10 12:57:00'");

        $this->mockStaticMethod(DB::class, '__callStatic')
            ->once()
            ->with(
                'get_col',
                ["SELECT product_id FROM wp_contextwp_table WHERE permanently_locked = 0 AND locked_until IS NOT NULL AND locked_until <= '2022-04-10 12:57:00'"]
            )
            ->andReturn(['id-1', 'id-2']);

        $this->assertSame(['id-1', 'id-2'], $repository->getLockedProductIds());
    }

    /**
     * @covers       \ContextWP\Repositories\ProductErrorsRepository::makeLockProductStrings()
     * @dataProvider providerCanMakeLockProductStrings
     *
     * @throws ReflectionException
     */
    public function testCanMakeLockProductStrings(
        string $consequenceReason,
        ?string $consequenceLockedUntil,
        ?string $consequenceBody,
        array $expected
    ): void {
        $consequence               = $this->createPartialMock(ErrorConsequence::class, ['getLockedUntil']);
        $consequence->productId    = 'pid';
        $consequence->reason       = $consequenceReason;
        $consequence->responseBody = $consequenceBody;

        $consequence->expects($this->once())
            ->method('getLockedUntil')
            ->willReturn($consequenceLockedUntil);

        $this->mockStaticMethod(DB::class, 'prepare')
            ->once()
            ->with(
                "(%s, %d, %s, %s)",
                $consequence->productId,
                (int) $consequence->isPermanentlyLocked(),
                $consequenceLockedUntil,
                $consequence->responseBody
            )
            ->andReturnUsing(function ($string, $pid, $isLocked, $lockedUntil, $response) {
                return sprintf(
                    $string,
                    $pid,
                    $isLocked,
                    is_null($lockedUntil) ? 'null' : $lockedUntil,
                    is_null($response) ? 'null' : $response
                );
            });

        $this->assertEqualsCanonicalizing(
            $expected,
            $this->invokeInaccessibleMethod(new ProductErrorsRepository(), 'makeLockProductStrings', [$consequence])
        );
    }

    /** @see testCanMakeLockProductStrings */
    public function providerCanMakeLockProductStrings(): Generator
    {
        yield 'permanently locked' => [
            'consequenceReason'      => ErrorConsequence::ProductNotFound,
            'consequenceLockedUntil' => null,
            'consequenceBody'        => null,
            'expected'               => [
                '(pid, 1, null, null)',
            ],
        ];

        yield 'locked until date' => [
            'consequenceReason'      => ErrorConsequence::ValidationError,
            'consequenceLockedUntil' => '2025-04-15 9:59:99',
            'consequenceBody'        => 'body',
            'expected'               => [
                "(pid, 0, 2025-04-15 9:59:99, body)",
            ]
        ];
    }

    /**
     * @covers \ContextWP\Repositories\ProductErrorsRepository::lockProducts()
     *
     * This test may not be a great idea because it will fail if the spacing/line breaks change in the query.
     */
    public function testCanLockProducts(): void
    {
        $repository   = $this->createPartialMock(ProductErrorsRepository::class, ['makeLockProductStrings', 'getTableName']);
        $consequences = [new ErrorConsequence('pid', ErrorConsequence::ValidationError)];

        $repository->expects($this->once())
            ->method('getTableName')
            ->willReturn('wp_contextwp');

        $repository->expects($this->once())
            ->method('makeLockProductStrings')
            ->with($consequences)
            ->willReturn(["('pid', 0, '2025-04-15 9:59:99', 'body')"]);

        $this->mockStaticMethod(DB::class, '__callStatic')
            ->with(
                'query',
                [
                    "INSERT INTO wp_contextwp (product_id, permanently_locked, locked_until, response_body)
                    VALUES ('pid', 0, '2025-04-15 9:59:99', 'body')
                    ON DUPLICATE KEY UPDATE
                        permanently_locked = VALUES(permanently_locked),
                        locked_until = VALUES(locked_until),
                        response_body = VALUES(response_body)"
                ]
            );

        $repository->lockProducts($consequences);

        $this->assertConditionsMet();
    }
}
