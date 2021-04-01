<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Component\Router;

use Throwable;
use Ulrack\CliApplication\Command\HelpCommand;
use GrizzIt\Cli\Common\Factory\IoFactoryInterface;
use GrizzIt\Command\Common\Command\InputInterface;
use GrizzIt\Command\Common\Command\OutputModeEnum;
use Ulrack\CliApplication\Component\Command\Input;
use GrizzIt\Command\Common\Command\OutputInterface;
use GrizzIt\Command\Common\Command\CommandInterface;
use GrizzIt\Validator\Component\Type\StringValidator;
use Ulrack\CliApplication\Command\ListCommandsCommand;
use GrizzIt\Cli\Common\Factory\ElementFactoryInterface;
use GrizzIt\Cli\Common\Generator\FormGeneratorInterface;
use Ulrack\CliApplication\Common\Router\RouterInterface;
use GrizzIt\Validator\Component\Textual\PatternValidator;
use GrizzIt\Services\Common\Factory\ServiceFactoryInterface;
use Ulrack\CliApplication\Exception\CommandNotFoundException;
use Ulrack\CliApplication\Exception\CommandCanNotExecuteException;
use Ulrack\CliApplication\Exception\MisconfiguredCommandException;
use Ulrack\CliApplication\Common\Dao\CommandConfigurationInterface;

class CommandRouter implements RouterInterface
{
    /**
     * Contains the registry with all commands.
     *
     * @var CommandConfigurationInterface
     */
    private $commandConfiguration;

    /**
     * Contains the service factory to create the command.
     *
     * @var ServiceFactoryInterface
     */
    private $serviceFactory;

    /**
     * Contains the output for the command.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * Contains the error element factory.
     *
     * @var ElementFactoryInterface
     */
    private $errorElementFactory;

    /**
     * Contains the IO factory.
     *
     * @var IoFactoryInterface
     */
    private $ioFactory;

    /**
     * Contains the form generator to add additional missing parameters.
     *
     * @var FormGeneratorInterface
     */
    private $formGenerator;

    /**
     * Constructor.
     *
     * @param CommandConfigurationInterface $commandConfiguration
     * @param ServiceFactoryInterface $serviceFactory
     * @param ElementFactoryInterface $errorElementFactory
     * @param IoFactoryInterface $ioFactory
     * @param OutputInterface $output
     * @param FormGeneratorInterface $formGenerator
     */
    public function __construct(
        CommandConfigurationInterface $commandConfiguration,
        ServiceFactoryInterface $serviceFactory,
        ElementFactoryInterface $errorElementFactory,
        IoFactoryInterface $ioFactory,
        OutputInterface $output,
        FormGeneratorInterface $formGenerator
    ) {
        $this->commandConfiguration = $commandConfiguration;
        $this->serviceFactory = $serviceFactory;
        $this->errorElementFactory = $errorElementFactory;
        $this->ioFactory = $ioFactory;
        $this->output = $output;
        $this->formGenerator = $formGenerator;
    }

    /**
     * Resolves the input to a command, executes it and returns the exit code.
     *
     * @param InputInterface $input
     *
     * @return int
     */
    public function __invoke(InputInterface $input): int
    {
        $command = $input->getCommand();
        $originalCommand = $command;
        try {
            $command = $this->findCommand($originalCommand);
            if ($input instanceof Input) {
                $input->loadConfiguration($command);
            }

            if ($input->isSetFlag('verbose')) {
                $this->output->setOutputMode(
                    OutputModeEnum::OUTPUT_MODE_VERBOSE()
                );
            }

            if ($input->isSetFlag('quiet')) {
                $this->output->setOutputMode(
                    OutputModeEnum::OUTPUT_MODE_QUIET()
                );
            }

            if ($input->isSetFlag('no-interaction')) {
                $this->ioFactory->setAllowReading(false);
            }

            $serviceKey = $command->getService();
            if ($input->isSetFlag('help')) {
                (new HelpCommand($command))->__invoke($input, $this->output);

                return 0;
            }

            if ($serviceKey !== '') {
                try {
                    foreach (
                        $this->getMissingParameters(
                            $input,
                            $command
                        ) as $key => $value
                    ) {
                        $input->setParameter($key, $value);
                    }
                } catch (MisconfiguredCommandException $exception) {
                    throw new CommandCanNotExecuteException(
                        $originalCommand,
                        $exception->getMessage()
                    );
                }

                /** @var CommandInterface $command */
                $this->serviceFactory
                    ->create($serviceKey)
                    ->__invoke($input, $this->output);

                return 0;
            }

            if (count($command->getCommands()) > 0) {
                (new ListCommandsCommand($command))->__invoke(
                    $input,
                    $this->output
                );

                return 0;
            }

            throw new CommandCanNotExecuteException(
                $originalCommand,
                'Service definition not configured.'
            );
        } catch (Throwable $exception) {
            $this->errorElementFactory->createBlock(
                substr($exception->getMessage(), 0, 1000),
                'error-block'
            )->render();

            $code = $exception->getCode();

            $trace = $exception->getTraceAsString();
            $i = 0;
            $this->output->writeLine(
                'Previous exceptions:',
                'text',
                true
            );

            while ($exception = $exception->getPrevious()) {
                $i++;
                $this->output->writeLine(
                    sprintf(
                        'Previous %d: %s',
                        $i,
                        $exception->getMessage()
                    ),
                    'text',
                    true
                );
            }

            $this->output->writeLine(
                '',
                'text',
                true
            );

            $this->output->writeLine(
                sprintf('Trace: %s', $trace),
                'text',
                true
            );

            return is_string($code) || !$code ? 1 : $code;
        }
    }

    /**
     * Resolves the command iteratively.
     *
     * @param array $command
     *
     * @return CommandConfigurationInterface
     *
     * @throws CommandNotFoundException When the command can not be found.
     */
    private function findCommand(
        array $command
    ): CommandConfigurationInterface {
        $configuration = $this->commandConfiguration;
        $originalCommand = $command;
        foreach ($command as $key => $item) {
            if ($configuration->hasCommand($item)) {
                $configuration = $configuration->getCommand($item);

                continue;
            }

            throw new CommandNotFoundException($originalCommand, $key);
        }

        return $configuration;
    }

    /**
     * Generates a form asks the user to fill in the blanks.
     *
     * @param InputInterface $input
     * @param CommandConfigurationInterface $configuration
     *
     * @return array
     *
     * @throws MisconfiguredCommandException When the command configuration is incorrect.
     */
    private function getMissingParameters(
        InputInterface $input,
        CommandConfigurationInterface $configuration
    ): array {
        $missing = false;
        $this->formGenerator->init(
            'Missing parameters',
            'The following parameters were required and missing. ' .
            'Please fill them in before execution procceeds.'
        );

        foreach ($configuration->getParameters() as $parameter) {
            if (
                isset($parameter['required'])
                && $parameter['required']
            ) {
                if (
                    !$input->hasParameter(
                        $parameter['long'] ?? $parameter['short']
                    )
                ) {
                    $missing = true;
                    if (
                        isset($parameter['hidden'])
                        && $parameter['hidden']
                    ) {
                        $this->createHiddenField($parameter);

                        continue;
                    } elseif (
                        isset($parameter['options'])
                        && is_array($parameter['options'])
                    ) {
                        $this->createAutocompletingField($parameter);

                        continue;
                    }

                    $this->createOpenField($parameter);
                }
            }
        }

        $form = $this->formGenerator->getForm();

        if ($missing) {
            $form->render();

            return $form->getInput();
        }

        return [];
    }

    /**
     * Creates the hidden field for the form generator.
     *
     * @param array $parameter
     *
     * @return void
     *
     * @throws MisconfiguredCommandException When the parameter is not configured correctly.
     */
    private function createHiddenField(array $parameter): void
    {
        if ($parameter['type'] === 'array') {
            $this->formGenerator->addHiddenArrayField(
                $parameter['long'] ?? $parameter['short'],
                true
            );

            return;
        } elseif (in_array($parameter['type'], ['string', 'number'])) {
            $this->formGenerator->addHiddenField(
                $parameter['long'] ?? $parameter['short'],
                true,
                sprintf(
                    'This field is required, and must be a %s',
                    $parameter['type']
                ),
                $parameter['type'] === 'string'
                    ? new StringValidator()
                    : new PatternValidator('[0-9]+')
            );

            return;
        }

        throw new MisconfiguredCommandException();
    }

    /**
     * Creates the autocompleting field for the form generator.
     *
     * @param array $parameter
     *
     * @return void
     *
     * @throws MisconfiguredCommandException When the parameter is not configured correctly.
     */
    private function createAutocompletingField(array $parameter): void
    {
        if ($parameter['type'] === 'array') {
            $this->formGenerator->addAutocompletingArrayField(
                $parameter['long'] ?? $parameter['short'],
                $parameter['options'],
                true
            );

            return;
        } elseif (in_array($parameter['type'], ['string', 'number'])) {
            $this->formGenerator->addAutocompletingField(
                $parameter['long'] ?? $parameter['short'],
                $parameter['options'],
                true
            );

            return;
        }

        throw new MisconfiguredCommandException();
    }

    /**
     * Creates the autocompleting field for the form generator.
     *
     * @param array $parameter
     *
     * @return void
     *
     * @throws MisconfiguredCommandException When the parameter is not configured correctly.
     */
    private function createOpenField(array $parameter): void
    {
        if ($parameter['type'] === 'array') {
            $this->formGenerator->addOpenArrayField(
                $parameter['long'] ?? $parameter['short'],
                true
            );

            return;
        } elseif (in_array($parameter['type'], ['string', 'number'])) {
            $this->formGenerator->addOpenField(
                $parameter['long'] ?? $parameter['short'],
                true,
                sprintf(
                    'This field is required, and must be a %s',
                    $parameter['type']
                ),
                $parameter['type'] === 'string'
                    ? new StringValidator()
                    : new NumberValidator()
            );

            return;
        }

        throw new MisconfiguredCommandException();
    }
}
