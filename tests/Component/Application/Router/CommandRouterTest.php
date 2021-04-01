<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Component\Router;

use PHPUnit\Framework\TestCase;
use GrizzIt\Command\Component\Command\Input;
use GrizzIt\Cli\Common\Element\FormInterface;
use GrizzIt\Cli\Common\Factory\IoFactoryInterface;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use Ulrack\CliApplication\Dao\CommandConfiguration;
use GrizzIt\Command\Common\Command\CommandInterface;
use GrizzIt\Cli\Common\Factory\ElementFactoryInterface;
use GrizzIt\Cli\Common\Generator\FormGeneratorInterface;
use Ulrack\CliApplication\Component\Router\CommandRouter;
use GrizzIt\Services\Common\Factory\ServiceFactoryInterface;
use Ulrack\CliApplication\Component\Command\Input as UlrackInput;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Component\Router\CommandRouter
 * @covers \Ulrack\CliApplication\Exception\CommandCanNotExecuteException
 * @covers \Ulrack\CliApplication\Exception\CommandNotFoundException
 * @covers \Ulrack\CliApplication\Exception\MisconfiguredCommandException
 */
class CommandRouterTest extends TestCase
{
    /**
     * @covers ::__invoke
     * @covers ::__construct
     * @covers ::findCommand
     * @covers ::getMissingParameters
     * @covers ::createHiddenField
     * @covers ::createAutocompletingField
     * @covers ::createOpenField
     *
     * @param InputInterface $input
     * @param CommandConfigurationInterface $commandConfiguration
     * @param int $expected
     *
     * @return void
     *
     * @dataProvider routingProvider
     */
    public function testInvoke(
        InputInterface $input,
        CommandConfigurationInterface $commandConfiguration,
        int $expected
    ): void {
        $serviceFactory = $this->createMock(ServiceFactoryInterface::class);
        $errorElementFactory = $this->createMock(ElementFactoryInterface::class);
        $ioFactory = $this->createMock(IoFactoryInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $formGenerator = $this->createMock(FormGeneratorInterface::class);
        $subject = new CommandRouter(
            $commandConfiguration,
            $serviceFactory,
            $errorElementFactory,
            $ioFactory,
            $output,
            $formGenerator
        );

        $form = $this->createMock(FormInterface::class);
        $formGenerator->method('getForm')->willReturn($form);
        $form->method('getInput')->willReturn(['foo' => 'bar']);
        $serviceFactory->method('create')->willReturn(
            $this->createMock(CommandInterface::class)
        );

        $this->assertEquals($expected, $subject->__invoke($input));
    }

    /**
     * Provides configurations for resolving routes.
     *
     * @return array
     */
    public function routingProvider(): array
    {
        $fooConfig = new CommandConfiguration();
        $fooConfig->addCommandConfiguration(
            'foo',
            new CommandConfiguration()
        );

        return [
            // Command not execute test
            [
                $this->createMock(UlrackInput::class),
                $this->createMock(CommandConfigurationInterface::class),
                126
            ],
            // Show help command
            [
                new UlrackInput(new Input([], [], ['help', 'verbose'])),
                $this->createMock(CommandConfigurationInterface::class),
                0
            ],
            // Show list command
            [
                new Input(),
                $fooConfig,
                0
            ],
            // Command not found in command
            [
                new UlrackInput(new Input(['foo', 'bar'], [], ['quiet'])),
                $fooConfig,
                127
            ],
            // Missing open parameter with form.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'foo',
                    'required' => true
                ]]),
                126
            ],
            // Missing hidden parameter with form.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'foo',
                    'required' => true,
                    'hidden' => true,
                ]]),
                126
            ],
            // Missing option parameter with form.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'foo',
                    'required' => true,
                    'options' => [],
                ]]),
                126
            ],
            // Missing option no interaction.
            [
                new UlrackInput(new Input(['foo'], [], ['no-interaction', 'quiet'])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'number',
                    'required' => true,
                ]]),
                1
            ],
            // Missing open option.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'string',
                    'required' => true,
                ]]),
                0
            ],
            // Missing array option.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'array',
                    'required' => true,
                ]]),
                0
            ],
            // Missing autocompleting option.
            [
                new Input(['foo'], [], []),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'string',
                    'required' => true,
                    'options' => []
                ]]),
                0
            ],
            // Missing array autocompleting option.
            [
                new Input(['foo'], [], []),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'array',
                    'required' => true,
                    'options' => []
                ]]),
                0
            ],
            // Missing hidden option.
            [
                new Input(['foo'], [], []),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'long' => 'foo',
                    'type' => 'string',
                    'required' => true,
                    'hidden' => true
                ]]),
                0
            ],
            // Missing hidden array option.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'short' => 'foo',
                    'type' => 'array',
                    'required' => true,
                    'hidden' => true
                ]]),
                0
            ],
            // Nothing missing.
            [
                new UlrackInput(new Input(['foo'], [], [])),
                $this->createCommandConfiguration('foo', 'bar', [[
                    'short' => 'foo',
                    'type' => 'array',
                    'required' => false,
                    'hidden' => true
                ]]),
                0
            ],
        ];
    }

    /**
     * Creates a command configuration.
     *
     * @param string $name
     * @param string $service
     * @param array $config
     *
     * @return CommandConfigurationInterface
     */
    private function createCommandConfiguration(
        string $name,
        string $service,
        array $configuration
    ): CommandConfigurationInterface {
        $config = new CommandConfiguration();
        $config->addCommandConfiguration(
            $name,
            new CommandConfiguration(
                $service,
                'description',
                $configuration
            )
        );

        return $config;
    }
}
