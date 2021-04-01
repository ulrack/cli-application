<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Common\Dao;

interface CommandConfigurationInterface
{
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
    ): void;

    /**
     * Retrieves the allowed flags for the command.
     *
     * @return array
     */
    public function getFlags(): array;

    /**
     * Retrieves the allowed parameters.
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Retrieves a command configuration nested inside the configuration.
     *
     * @param string $command
     *
     * @return CommandConfigurationInterface
     */
    public function getCommand(string $command): CommandConfigurationInterface;

    /**
     * Retrieves a command configuration nested inside the configuration.
     *
     * @param string $command
     *
     * @return bool
     */
    public function hasCommand(string $command): bool;

    /**
     * Retrieves the service key.
     *
     * @return string
     */
    public function getService(): string;

    /**
     * Retrieves all nested commands.
     *
     * @return string[]
     */
    public function getCommands(): array;

    /**
     * Retrieves the description for the command configuration.
     *
     * @return string
     */
    public function getDescription(): string;
}
