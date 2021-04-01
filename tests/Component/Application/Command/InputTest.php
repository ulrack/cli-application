<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Component\Command;

use PHPUnit\Framework\TestCase;
use Ulrack\CliApplication\Component\Command\Input;
use GrizzIt\Command\Component\Command\Input as GrizzItInput;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Component\Command\Input
 */
class InputTest extends TestCase
{
    /**
     * A few simple getter method tests.
     *
     * @covers ::hasParameter
     * @covers ::getParameter
     * @covers ::isSetFlag
     * @covers ::getCommand
     * @covers ::getParameters
     * @covers ::getFlags
     * @covers ::__construct
     *
     * @return void
     */
    public function testGetters(): void
    {
        $command = ['foo'];
        $parameters = ['bar' => 'baz'];
        $flags = ['qux'];
        $subject = new Input(new GrizzItInput($command, $parameters, $flags));

        $this->assertEquals($command, $subject->getCommand());
        $this->assertEquals($parameters, $subject->getParameters());
        $this->assertEquals($flags, $subject->getFlags());
        $this->assertEquals(true, $subject->isSetFlag('qux'));
        $this->assertEquals(true, $subject->hasParameter('bar'));
        $this->assertEquals('baz', $subject->getParameter('bar'));
    }

    /**
     * Test if the variable is going to be set correctly.
     *
     * @covers ::hasParameter
     * @covers ::setParameter
     * @covers ::__construct
     *
     * @return void
     */
    public function testSetter(): void
    {
        $subject = new Input(new GrizzItInput([], [], []));

        $this->assertEquals(false, $subject->hasParameter('baz'));
        $subject->setParameter('baz', 'foo');
        $this->assertEquals(true, $subject->hasParameter('baz'));
    }

    /**
     * Test if the command configuration works as expected.
     *
     * @covers ::loadConfiguration
     * @covers ::hasParameter
     * @covers ::isSetFlag
     * @covers ::getParameter
     * @covers ::__construct
     *
     * @return void
     */
    public function testLoadingCommandConfiguration(): void
    {
        $command = ['foo'];
        $parameters = ['bar' => 'baz', 'ba' => 'b'];
        $flags = ['qux'];
        $subject = new Input(new GrizzItInput($command, $parameters, $flags));

        // Pre configuration checks.
        $this->assertEquals(false, $subject->hasParameter('b'));
        $this->assertEquals(null, $subject->getParameter('b'));
        $this->assertEquals(false, $subject->hasParameter('baz'));
        $this->assertEquals(null, $subject->getParameter('baz'));
        $this->assertEquals(false, $subject->isSetFlag('q'));

        // Add the configuration.
        $commandConfiguration = $this->createMock(
            CommandConfigurationInterface::class
        );

        $commandConfiguration->expects(static::exactly(4))
            ->method('getParameters')
            ->willReturn([
                [
                    'long' => 'bar',
                    'short' => 'b',
                    'type' => 'number',
                    'required' => false,
                    'description' => 'bar description'
                ],
                [
                    'long' => 'baz',
                    'short' => 'ba',
                    'type' => 'string',
                    'required' => true,
                    'description' => 'baz description'
                ]
            ]);

        $commandConfiguration->expects(static::once())
            ->method('getFlags')
            ->willReturn([
                [
                    'long' => 'qux',
                    'short' => 'q',
                    'description' => 'qux description'
                ]
            ]);

        $subject->loadConfiguration($commandConfiguration);

        // Verify that the aliases can now be resolved.
        $this->assertEquals(true, $subject->hasParameter('b'));
        $this->assertEquals(true, $subject->hasParameter('baz'));
        $this->assertEquals(true, $subject->isSetFlag('q'));
        $this->assertEquals('baz', $subject->getParameter('b'));
        $this->assertEquals('b', $subject->getParameter('baz'));
    }
}
