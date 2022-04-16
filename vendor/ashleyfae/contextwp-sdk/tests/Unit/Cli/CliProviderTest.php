<?php
/**
 * CliProviderTest.php
 *
 * @package   contextwp-sdk
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace ContextWP\Tests\Unit\Cli;

use ContextWP\Cli\CliProvider;
use ContextWP\Cli\Commands\SendCheckInsCommand;
use ContextWP\Tests\TestCase;
use Generator;
use Mockery;

class CliProviderTest extends TestCase
{
    /**
     * @covers       \ContextWP\Cli\CliProvider::load()
     * @dataProvider providerCanLoad
     */
    public function testCanLoad(bool $shouldLoad): void
    {
        $provider = $this->createPartialMock(CliProvider::class, ['shouldLoad', 'registerCommands']);

        $provider->expects($this->once())
            ->method('shouldLoad')
            ->willReturn($shouldLoad);

        $provider->expects($shouldLoad ? $this->once() : $this->never())
            ->method('registerCommands')
            ->willReturn(null);

        $provider->load();

        $this->assertConditionsMet();
    }

    /** @see testCanLoad */
    public function providerCanLoad(): Generator
    {
        yield 'should load' => [true];
        yield 'do not load' => [false];
    }

    /**
     * @covers \ContextWP\Cli\CliProvider::registerCommands()
     */
    public function testCanRegisterCommands(): void
    {
        $provider = $this->createPartialMock(CliProvider::class, ['registerCommand']);
        $this->setInaccessibleProperty($provider, 'commands', ['Command1', 'Command2', 'Command3']);

        $provider->expects($this->exactly(3))
            ->method('registerCommand')
            ->withConsecutive(['Command1'], ['Command2'], ['Command3'])
            ->willReturnOnConsecutiveCalls(null, null, null);

        $this->invokeInaccessibleMethod($provider, 'registerCommands');

        $this->assertConditionsMet();
    }

    /**
     * @covers \ContextWP\Cli\CliProvider::registerCommand()
     */
    public function testCanRegisterCommand(): void
    {
        $wpCli = Mockery::mock('alias:WP_CLI');
        $wpCli->expects('add_command')
            ->once()
            ->with('contextwp checkin', SendCheckInsCommand::class)
            ->andReturnNull();

        $this->invokeInaccessibleMethod(new CliProvider(), 'registerCommand', SendCheckInsCommand::class);

        $this->assertConditionsMet();
    }
}
