<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Component\Command;

use GrizzIt\Command\Common\Command\InputInterface;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

class Input implements InputInterface
{
    /**
     * Contains the loaded command configuration.
     *
     * @var CommandConfigurationInterface
     */
    private ?CommandConfigurationInterface $commandConfiguration = null;

    /**
     * Constructor
     *
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * Loads configuration into the input.
     *
     * @param CommandConfigurationInterface $commandConfiguration
     *
     * @return void
     */
    public function loadConfiguration(
        CommandConfigurationInterface $commandConfiguration
    ): void {
        $this->commandConfiguration = $commandConfiguration;
    }

    /**
     * Checks whether the flag is set.
     *
     * @param string $flag
     *
     * @return bool
     */
    public function hasParameter(string $parameter): bool
    {
        if ($this->commandConfiguration !== null) {
            foreach (
                $this->commandConfiguration
                ->getParameters() as $parameterInput
            ) {
                $long = $parameterInput['long'] ?? '';
                $short = $parameterInput['short'] ?? '';
                if ($long === $parameter || $short === $parameter) {
                    return $this->input->hasParameter($long)
                        || $this->input->hasParameter($short);
                }
            }
        }

        return $this->input->hasParameter($parameter);
    }

    /**
     * Sets the value of a parameter.
     *
     * @param string $parameter
     * @param mixed $value
     *
     * @return void
     */
    public function setParameter(string $parameter, $value): void
    {
        $this->input->setParameter($parameter, $value);
    }

    /**
     * Retrieves the parameter from the input.
     *
     * @param string $parameter
     *
     * @return mixed
     */
    public function getParameter(string $parameter)
    {
        if ($this->commandConfiguration !== null) {
            foreach ($this->commandConfiguration->getParameters() as $parameterInput) {
                $long = $parameterInput['long'] ?? '';
                $short = $parameterInput['short'] ?? '';
                if ($long === $parameter || $short === $parameter) {
                    if ($this->input->hasParameter($long)) {
                        return $this->input->getParameter($long);
                    } elseif ($this->input->hasParameter($short)) {
                        return $this->input->getParameter($short);
                    }
                }
            }
        }

        return $this->input->getParameter($parameter);
    }

    /**
     * Checks whether the flag is set.
     *
     * @param string $flag
     *
     * @return bool
     */
    public function isSetFlag(string $flag): bool
    {
        if ($this->commandConfiguration !== null) {
            foreach ($this->commandConfiguration->getFlags() as $configFlag) {
                if (
                    $configFlag['long'] === $flag
                    || $configFlag['short'] === $flag
                ) {
                    return $this->input->isSetFlag($configFlag['long'])
                        || $this->input->isSetFlag($configFlag['short']);
                }
            }
        }

        return $this->input->isSetFlag($flag);
    }

    /**
     * Returns the command.
     *
     * @return string[]
     */
    public function getCommand(): array
    {
        return $this->input->getCommand();
    }

    /**
     * Returns the parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->input->getParameters();
    }

    /**
     * Returns the flags.
     *
     * @return array
     */
    public function getFlags(): array
    {
        return $this->input->getFlags();
    }
}
