<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Command;

use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputInterface;
use GrizzIt\Command\Common\Command\CommandInterface;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

class HelpCommand implements CommandInterface
{
    /**
     * Contains the current command configuration.
     *
     * @var CommandConfigurationInterface
     */
    private $commandConfiguration;

    /**
     * Constructor.
     *
     * @param CommandConfigurationInterface $commandConfiguration
     */
    public function __construct(
        CommandConfigurationInterface $commandConfiguration
    ) {
        $this->commandConfiguration = $commandConfiguration;
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
        $output->outputText(
            'Command: ' . implode(' ', $input->getCommand()),
            true,
            'title'
        );

        $description = $this->commandConfiguration->getDescription();
        if (!empty($description)) {
            $output->outputText('Description: ' . $description);
        }

        if (count($this->commandConfiguration->getParameters()) > 0) {
            $output->writeLine('');

            $output->outputText(
                'Parameters: ',
                true,
                'title'
            );

            $output->outputExplainedList(
                $this->constructParametersList()
            );
        }

        if (count($this->commandConfiguration->getFlags()) > 0) {
            $output->writeLine('');

            $output->outputText(
                'Flags: ',
                true,
                'title'
            );

            $output->outputExplainedList(
                $this->constructFlagsList()
            );
        }
    }

    /**
     * Constructs the explained flags list.
     *
     * @return array
     */
    private function constructFlagsList(): array
    {
        $list = [];

        foreach ($this->commandConfiguration->getFlags() as $flag) {
            $options = [];

            if (isset($flag['long'])) {
                $options[] = $flag['long'];
            }

            if (isset($flag['short'])) {
                $options[] = $flag['short'];
            }

            $list[sprintf(
                '[%s]',
                implode('|', $options)
            )] = $flag['description'] ?? '';
        }

        return $list;
    }

    /**
     * Constructs the explained parameters list.
     *
     * @return array
     */
    private function constructParametersList(): array
    {
        $list = [];

        foreach ($this->commandConfiguration->getParameters() as $parameter) {
            $options = [];

            if (isset($parameter['long'])) {
                $options[] = $parameter['long'];
            }

            if (isset($parameter['short'])) {
                $options[] = $parameter['short'];
            }

            $list[sprintf(
                '[%s](%s)%s',
                implode('|', $options),
                $parameter['type'],
                isset($parameter['required']) && $parameter['required']
                    ? '*'
                    : ''
            )] = $parameter['description'] ?? '';
        }

        return $list;
    }
}
