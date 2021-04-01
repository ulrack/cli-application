<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Command;

use PHPUnit\Framework\TestCase;
use GrizzIt\Validator\Common\ValidatorInterface;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use GrizzIt\Configuration\Common\RegistryInterface;
use Ulrack\Kernel\Common\Manager\ValidationManagerInterface;
use GrizzIt\JsonSchema\Common\SchemaValidatorFactoryInterface;
use Ulrack\CliApplication\Command\ValidateConfigurationCommand;
use Ulrack\Kernel\Common\Manager\ConfigurationManagerInterface;
use Ulrack\CliApplication\Exception\UnpassedValidationException;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Command\ValidateConfigurationCommand
 * @covers \Ulrack\CliApplication\Exception\UnpassedValidationException
 */
class ValidateConfigurationCommandTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::specialValidation
     */
    public function testCommand(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $configurationManager = $this->createMock(
            ConfigurationManagerInterface::class
        );

        $validationManager = $this->createMock(
            ValidationManagerInterface::class
        );

        $configRegistry = $this->createMock(RegistryInterface::class);
        $validatorFactory = $this->createMock(
            SchemaValidatorFactoryInterface::class
        );

        $validator = $this->createMock(ValidatorInterface::class);

        $configurationManager->expects(static::once())
            ->method('getConfigRegistry')
            ->willReturn($configRegistry);

        $configRegistry->expects(static::once())
            ->method('toArray')
            ->willReturn([
                'services' => [
                    'foo' => [
                        [
                            '$schema' => 'foo.json',
                            'bar' => 'baz'
                        ]
                    ]
                ]
            ]);

        $subject = new ValidateConfigurationCommand(
            $configurationManager,
            $validationManager,
            [
                [
                    'key' => 'foo',
                    'schema' => 'foo.json'
                ],
                [
                    'key' => 'services',
                    'schema' => 'services.schema.json'
                ]
            ]
        );

        $validationManager->expects(static::exactly(3))
            ->method('getValidatorFactory')
            ->willReturn($validatorFactory);

        $validatorFactory->expects(static::exactly(2))
            ->method('createFromRemoteFile')
            ->withConsecutive(
                ['foo.json'],
                ['services.schema.json']
            )->willReturn($validator);

        $validator->expects(static::once())
            ->method('__invoke')
            ->with((object) [
                '$schema' => 'foo.json',
                'bar' => 'baz'
            ])->willReturn(true);

        $subject->__invoke($input, $output);
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::specialValidation
     */
    public function testCommandException(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $configurationManager = $this->createMock(
            ConfigurationManagerInterface::class
        );

        $validationManager = $this->createMock(
            ValidationManagerInterface::class
        );

        $validatorFactory = $this->createMock(
            SchemaValidatorFactoryInterface::class
        );

        $configRegistry = $this->createMock(RegistryInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);

        $configurationManager->expects(static::once())
            ->method('getConfigRegistry')
            ->willReturn($configRegistry);

        $configRegistry->expects(static::once())
            ->method('toArray')
            ->willReturn([
                'services' => [
                    'parameters' => [
                        ['foo' => 'bar']
                    ]
                ],
                'foo' => [
                    [
                        '$schema' => 'foo.json',
                        'bar' => 'baz'
                    ]
                ]
            ]);

        $validationManager->expects(static::exactly(2))
            ->method('getValidatorFactory')
            ->willReturn($validatorFactory);

        $validatorFactory->expects(static::exactly(2))
            ->method('createFromRemoteFile')
            ->withConsecutive(
                ['foo.json'],
                ['parameters.schema.json']
            )->willReturn($validator);

        $validator->expects(static::exactly(2))
            ->method('__invoke')
            ->willReturn(false);

        $subject = new ValidateConfigurationCommand(
            $configurationManager,
            $validationManager,
            [
                [
                    'key' => 'parameters',
                    'schema' => 'parameters.schema.json'
                ]
            ]
        );

        $this->expectException(UnpassedValidationException::class);

        $subject->__invoke($input, $output);
    }
}
