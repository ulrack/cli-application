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

class ListCommandsCommand implements CommandInterface
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
            'Command list: ' . implode(' ', $input->getCommand()),
            true,
            'title'
        );

        $description = $this->commandConfiguration->getDescription();
        if (!empty($description)) {
            $output->outputText('Description: ' . $description);
        }

        // Output an empty line.
        $output->writeLine('');

        $output->outputExplainedList(
            $this->constructCommandList($this->commandConfiguration),
            'command-explained-list-key',
            'command-explained-list-description'
        );

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

    /**
     * Constructs the command list from the configuration.
     *
     * @param CommandConfigurationInterface $configuration
     * @param string $prefix
     *
     * @return array
     */
    private function constructCommandList(
        CommandConfigurationInterface $configuration,
        string $prefix = ''
    ): array {
        $list = [];

        foreach ($configuration->getCommands() as $command) {
            $subConfiguration = $configuration->getCommand($command);
            $list[$prefix . $command] = $subConfiguration->getDescription();
            $list = array_merge($list, $this->constructCommandList(
                $subConfiguration,
                $prefix . $command . '.'
            ));
        }

        return $list;
    }
}
