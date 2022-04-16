<?php
/**
 * TableManagerTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Database;

use ContextWP\Contracts\DatabaseTable;
use ContextWP\Database\TableManager;
use ContextWP\Database\Tables\ProductErrorsTable;
use ContextWP\Tests\TestCase;
use ReflectionException;

class TableManagerTest extends TestCase
{
    /**
     * @covers       \ContextWP\Database\TableManager::updateOrCreateTables()
     * @dataProvider providerCanUpdateOrCreateTables
     */
    public function testUpdateOrCreateTables(bool $needsUpgrade): void
    {
        $mockedTable = \Mockery::mock(DatabaseTable::class);
        $mockedTable->expects('needsUpgrade')
            ->once()
            ->andReturn($needsUpgrade);
        $mockedTable->expects('updateOrCreate')
            ->times($needsUpgrade ? 1 : 0)
            ->andReturnNull();

        $manager = $this->createPartialMock(TableManager::class, ['getTables']);
        $manager->expects($this->once())
            ->method('getTables')
            ->willReturn([$mockedTable]);

        $manager->updateOrCreateTables();

        $this->assertConditionsMet();
    }

    /** @see testUpdateOrCreateTables */
    public function providerCanUpdateOrCreateTables(): \Generator
    {
        yield 'needs upgrade' => [true];
        yield 'doesn\'t need upgrade' => [false];
    }

    /**
     * @covers \ContextWP\Database\TableManager::getTables()
     * @throws ReflectionException
     */
    public function testCanGetTables(): void
    {
        $manager = new TableManager();
        $this->setInaccessibleProperty($manager, 'tables', [ProductErrorsTable::class]);

        $tables = $this->invokeInaccessibleMethod($manager, 'getTables');

        $this->assertCount(1, $tables);
        $this->assertInstanceOf(ProductErrorsTable::class, $tables[0]);
    }
}
