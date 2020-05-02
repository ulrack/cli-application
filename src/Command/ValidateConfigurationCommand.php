<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Command;

use Ulrack\Command\Common\Command\InputInterface;
use Ulrack\Command\Common\Command\OutputInterface;
use Ulrack\Command\Common\Command\CommandInterface;
use Ulrack\Kernel\Common\Manager\ValidationManagerInterface;
use Ulrack\Kernel\Common\Manager\ConfigurationManagerInterface;
use Ulrack\CliApplication\Exception\UnpassedValidationException;

class ValidateConfigurationCommand implements CommandInterface
{
    /**
     * Contains the configuration manager.
     *
     * @var ConfigurationManagerInterface
     */
    private $configurationManager;

    /**
     * Contains the validation manager.
     *
     * @var ValidationManagerInterface
     */
    private $validationManager;

    /**
     * Constructor.
     *
     * @param ConfigurationManagerInterface $configurationManager
     * @param ValidationManagerInterface $validationManager
     */
    public function __construct(
        ConfigurationManagerInterface $configurationManager,
        ValidationManagerInterface $validationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->validationManager = $validationManager;
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output
    ): void {
        $configRegistry = $this->configurationManager->getConfigRegistry()
            ->toArray();
        $validatorFactory = $this->validationManager->getValidatorFactory();
        $errorMessages = [];

        foreach ($configRegistry as $configGroup) {
            foreach ($configGroup as $entry) {
                if (is_array($entry) && isset($entry['$schema'])) {
                    $validator = $validatorFactory->createFromRemoteFile(
                        $entry['$schema']
                    );

                    if (
                        !$validator->__invoke(
                            json_decode(json_encode($entry))
                        )
                    ) {
                        $errorMessages[] = sprintf(
                            'Invalid configuration found at: %s',
                            json_encode($entry, JSON_PRETTY_PRINT)
                        );
                    }
                }
            }
        }

        $errorMessages = array_merge($errorMessages, $this->specialValidation(
            'parameters',
            'parameters.schema.json',
            $configRegistry
        ), $this->specialValidation(
            'services',
            'services.schema.json',
            $configRegistry
        ), $this->specialValidation(
            'preferences',
            'preferences.schema.json',
            $configRegistry
        ));

        if (count($errorMessages) > 0) {
            throw new UnpassedValidationException(...$errorMessages);
        }

        $output->outputBlock('All configuration passed.', 'success-block');
    }

    /**
     * Performs special validation for files that do not support the $schema entry.
     *
     * @param string $key
     * @param string $schema
     * @param array $configRegistry
     *
     * @return string[]
     */
    private function specialValidation(
        string $key,
        string $schema,
        array $configRegistry
    ): array {
        $errorMessages = [];
        $validator = $this->validationManager->getValidatorFactory()
            ->createFromRemoteFile($schema);

        if (isset($configRegistry[$key])) {
            foreach ($configRegistry[$key] as $entry) {
                if (
                    !$validator->__invoke(
                        json_decode(json_encode($entry))
                    )
                ) {
                    $errorMessages[] = sprintf(
                        'Invalid configuration found at: %s',
                        json_encode($entry, JSON_PRETTY_PRINT)
                    );
                }
            }
        }

        return $errorMessages;
    }
}
