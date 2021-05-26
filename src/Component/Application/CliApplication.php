<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Component\Application;

use Ulrack\Kernel\Common\ApplicationInterface;
use Ulrack\CliApplication\Component\Command\Input;
use GrizzIt\Configuration\Common\RegistryInterface;
use Ulrack\CliApplication\Dao\CommandConfiguration;
use Ulrack\Kernel\Common\Manager\ServiceManagerInterface;
use Ulrack\Kernel\Common\Manager\ConfigurationManagerInterface;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

class CliApplication implements ApplicationInterface
{
    /**
     * Contains the arguments passed to the application.
     *
     * @var array
     */
    private $arguments;

    /**
     * Contains the exit code of the application.
     *
     * @var int
     */
    private $exitCode = 1;

    /**
     * Contains the commands registered in the application.
     *
     * @var CommandConfigurationInterface[]
     */
    private $commands = [];

    /**
     * Constructor.
     *
     * @param array $arguments
     */
    public function __construct(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Runs the application.
     *
     * @param ServiceManagerInterface $serviceManager
     *
     * @return void
     */
    public function run(ServiceManagerInterface $serviceManager): void
    {
        $serviceFactory = $serviceManager->getServiceFactory();
        $themeKey = $serviceFactory->create('parameters.cli-theme');
        $theme = $serviceFactory->create($themeKey)->getTheme();
        $serviceManager->registerService('cli.theme', $theme);

        $this->loadCommands(
            $serviceFactory->create('services.cli.command-configuration'),
            $serviceFactory->create('internal.core.configuration.manager')
                ->getConfigRegistry()
        );

        $commandRouter = $serviceFactory->create('services.cli.command-router');
        $inputFactory = $serviceFactory->create('services.cli.input-factory');

        $this->exitCode = $commandRouter->__invoke(
            new Input($inputFactory->create($this->arguments))
        );
    }

    /**
     * Loads the commands from the configuration.
     *
     * @param CommandConfigurationInterface $commandConfiguration
     * @param ConfigurationManagerInterface $configurationManager
     *
     * @return void
     */
    private function loadCommands(
        CommandConfigurationInterface $commandConfiguration,
        RegistryInterface $configRegistry
    ): void {
        $newRegistration = false;

        foreach ($configRegistry->get('command') as $command) {
            $commandKey = $command['command'];

            if (isset($command['parent']) && $command['parent'] !== '') {
                $commandKey = $command['parent'] . '.' . $commandKey;
            }

            $registerConfig = $commandConfiguration;
            if (
                !isset($command['parent']) ||
                isset($this->commands[$command['parent']])
            ) {
                if (isset($command['parent'])) {
                    $registerConfig = $this->commands[$command['parent']];
                }

                if (!isset($this->commands[$commandKey])) {
                    $this->commands[$commandKey] = new CommandConfiguration(
                        $command['service'] ?? '',
                        $command['description'] ?? '',
                        $command['parameters'] ?? [],
                        $command['flags'] ?? []
                    );

                    $registerConfig->addCommandConfiguration(
                        $command['command'],
                        $this->commands[$commandKey]
                    );

                    $newRegistration = true;
                }
            }
        }

        if ($newRegistration) {
            $this->loadCommands($commandConfiguration, $configRegistry);
        }
    }

    /**
     * Retrieves the exit code.
     *
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
