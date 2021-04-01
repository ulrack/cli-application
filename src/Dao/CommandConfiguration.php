<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Dao;

use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

class CommandConfiguration implements CommandConfigurationInterface
{
    /**
     * Contains the service configuration for the current command.
     *
     * @var string
     */
    private $service;

    /**
     * Retrieves the description of the command.
     *
     * @var string
     */
    private $description;

    /**
     * Contains all registered nested commands.
     *
     * @var CommandConfigurationInterface[]
     */
    private $commands = [];

    /**
     * Contains the parameters for the current configuration.
     *
     * @var array
     */
    private $parameters;

    /**
     * Contains the flags for the current configuration.
     *
     * @var array
     */
    private $flags;

    /**
     * Constructor.
     *
     * @param string $service
     * @param string $description
     * @param array $parameters
     * @param array $flags
     */
    public function __construct(
        string $service = '',
        string $description = '',
        array $parameters = [],
        array $flags = []
    ) {
        $this->service = $service;
        $this->description = $description;
        $this->parameters = $parameters;
        $this->flags = array_merge(
            $flags,
            [
                [
                    'long' => 'help',
                    'short' => 'h',
                    'description' => 'Explains the command.'
                ],
                [
                    'long' => 'no-interaction',
                    'short' => 'ni',
                    'description' => 'Prevents interaction during the execution of a command.'
                ],
                [
                    'long' => 'verbose',
                    'short' => 'v',
                    'description' => 'Displays verbose output for a command.'
                ],
                [
                    'long' => 'quiet',
                    'short' => 'q',
                    'description' => 'Silences all output.'
                ]
            ]
        );
    }

    /**
     * Adds a nested command configuration to the configuration.
     *
     * @param string $command
     * @param CommandConfigurationInterface $configuration
     *
     * @return void
     */
    public function addCommandConfiguration(
        string $command,
        CommandConfigurationInterface $configuration
    ): void {
        $this->commands[$command] = $configuration;
    }

    /**
     * Retrieves the allowed flags for the command.
     *
     * @return array
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * Retrieves the allowed parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Retrieves a command configuration nested inside the configuration.
     *
     * @param string $command
     *
     * @return CommandConfigurationInterface
     */
    public function getCommand(string $command): CommandConfigurationInterface
    {
        return $this->commands[$command];
    }

    /**
     * Retrieves a command configuration nested inside the configuration.
     *
     * @param string $command
     *
     * @return bool
     */
    public function hasCommand(string $command): bool
    {
        return isset($this->commands[$command]);
    }

    /**
     * Retrieves the service key.
     *
     * @return string
     */
    public function getService(): string
    {
        return $this->service;
    }

    /**
     * Retrieves all nested commands.
     *
     * @return string[]
     */
    public function getCommands(): array
    {
        return array_keys($this->commands);
    }

    /**
     * Retrieves the description for the command configuration.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
