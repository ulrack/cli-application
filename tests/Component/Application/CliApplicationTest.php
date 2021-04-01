<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Component\Application;

use PHPUnit\Framework\TestCase;
use GrizzIt\Command\Factory\InputFactory;
use GrizzIt\Configuration\Common\RegistryInterface;
use GrizzIt\Cli\Common\Theme\ApplicationThemeInterface;
use Ulrack\CliApplication\Common\Router\RouterInterface;
use Ulrack\Kernel\Common\Manager\ServiceManagerInterface;
use GrizzIt\Services\Common\Factory\ServiceFactoryInterface;
use Ulrack\CliApplication\Component\Application\CliApplication;
use Ulrack\Kernel\Common\Manager\ConfigurationManagerInterface;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Component\Application\CliApplication
 */
class CliApplicationTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::run
     * @covers ::loadCommands
     * @covers ::getExitCode
     */
    public function testApplication(): void
    {
        $subject = new CliApplication(['bin/application', 'foo']);

        $serviceManager = $this->createMock(ServiceManagerInterface::class);
        $serviceFactory = $this->createMock(ServiceFactoryInterface::class);
        $configurationManager = $this->createMock(ConfigurationManagerInterface::class);
        $configRegistry = $this->createMock(RegistryInterface::class);

        $serviceManager->expects(static::once())
            ->method('getServiceFactory')
            ->willReturn($serviceFactory);

        $serviceFactory->expects(static::exactly(6))
            ->method('create')
            ->withConsecutive(
                ['parameters.cli-theme'],
                ['services.cli.default-theme'],
                ['services.cli.command-configuration'],
                ['internal.core.configuration.manager'],
                ['services.cli.command-router'],
                ['services.cli.input-factory']
            )->willReturnOnConsecutiveCalls(
                'services.cli.default-theme',
                $this->createMock(ApplicationThemeInterface::class),
                $this->createMock(CommandConfigurationInterface::class),
                $configurationManager,
                $this->createMock(RouterInterface::class),
                new InputFactory()
            );

        $configurationManager->expects(static::once())
            ->method('getConfigRegistry')
            ->willReturn($configRegistry);

        $configRegistry->expects(static::exactly(3))
            ->method('get')
            ->with('command')
            ->willReturn(
                [
                    [
                        'parent' => 'my',
                        'command' => 'command'
                    ],
                    [
                        'command' => 'my'
                    ]
                ]
            );

        $subject->run($serviceManager);

        $this->assertEquals(0, $subject->getExitCode());
    }
}
