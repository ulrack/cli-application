<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Command;

use PHPUnit\Framework\TestCase;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use Ulrack\CliApplication\Command\ListCommandsCommand;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Command\ListCommandsCommand
 */
class ListCommandsCommandTest extends TestCase
{
    /**
     * @covers ::__invoke
     * @covers ::__construct
     * @covers ::constructFlagsList
     * @covers ::constructParametersList
     * @covers ::constructCommandList
     *
     * @return void
     */
    public function testInvoke(): void
    {
        $commandConfiguration = $this->createMock(CommandConfigurationInterface::class);
        $subject = new ListCommandsCommand($commandConfiguration);

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $commandConfiguration->expects(static::once())
            ->method('getDescription')
            ->willReturn('foo');

            $commandConfiguration->expects(static::once())
            ->method('getCommands')
            ->willReturn(['foo']);

        $commandConfiguration->expects(static::once())
            ->method('getCommand')
            ->with('foo')
            ->willReturn(
                $this->createConfiguredMock(
                    CommandConfigurationInterface::class,
                    [
                        'getDescription' => 'foo description',
                        'getCommands' => []
                    ]
                )
            );

        $commandConfiguration->expects(static::exactly(2))
            ->method('getParameters')
            ->willReturn([
                [
                    'long' => 'foo',
                    'short' => 'f',
                    'type' => 'string',
                    'required' => true,
                    'description' => 'foo description'
                ],
                [
                    'long' => 'bar',
                    'short' => 'b',
                    'type' => 'number',
                    'required' => false,
                    'description' => 'foo description'
                ]
            ]);

        $commandConfiguration->expects(static::exactly(2))
            ->method('getFlags')
            ->willReturn([
                [
                    'long' => 'baz',
                    'short' => 'b',
                    'description' => 'baz description'
                ],
                [
                    'long' => 'qux',
                    'short' => 'q',
                    'description' => 'qux description'
                ]
            ]);

        $subject->__invoke($input, $output);
    }
}
