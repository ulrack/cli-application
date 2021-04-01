<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Dao;

use PHPUnit\Framework\TestCase;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;
use Ulrack\CliApplication\Dao\CommandConfiguration;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Dao\CommandConfiguration
 */
class CommandConfigurationTest extends TestCase
{
    /**
     * @covers ::addCommandConfiguration
     * @covers ::getFlags
     * @covers ::getParameters
     * @covers ::getCommand
     * @covers ::hasCommand
     * @covers ::getService
     * @covers ::getCommands
     * @covers ::getDescription
     * @covers ::__construct
     *
     * @return void
     */
    public function testConfiguration(): void
    {
        $service = 'service';
        $description = 'description';
        $parameters = ['parameter' => 'value'];
        $flags = [
            [
                'long' => 'flag',
                'short' => 'f',
                'description' => 'A flag'
            ]
        ];

        $allFlags = array_merge(
            $flags,
            (new CommandConfiguration())->getFlags()
        );

        $subject = new CommandConfiguration($service, $description, $parameters, $flags);

        $this->assertEquals($allFlags, $subject->getFlags());
        $this->assertEquals($parameters, $subject->getParameters());
        $this->assertEquals(false, $subject->hasCommand('command'));
        $this->assertEquals($service, $subject->getService());
        $this->assertEquals([], $subject->getCommands());
        $this->assertEquals($description, $subject->getDescription());

        $command = 'command';
        $configuration = $this->createMock(CommandConfigurationInterface::class);
        $subject->addCommandConfiguration($command, $configuration);

        $this->assertEquals(true, $subject->hasCommand('command'));
        $this->assertEquals($configuration, $subject->getCommand('command'));
        $this->assertEquals([$command], $subject->getCommands());
    }
}
