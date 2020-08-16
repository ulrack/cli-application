<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Command;

use Ulrack\Command\Common\Command\InputInterface;
use Ulrack\Command\Common\Command\OutputInterface;
use Ulrack\Command\Common\Command\CommandInterface;
use Ulrack\Kernel\Common\Manager\CacheManagerInterface;

class CacheClearCommand implements CommandInterface
{
    /**
     * Contains the cache manager.
     *
     * @var CacheManagerInterface
     */
    private $cacheManager;

    /**
     * Constructor.
     *
     * @param CacheManagerInterface $cacheManager
     */
    public function __construct(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
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
        $caches = $input->getParameter('cache');
        if ($caches === null) {
            $output->writeLine('Clearing caches.');
            foreach (
                $this->cacheManager
                    ->getCacheFileSystem()
                    ->list('') as $cacheDir
            ) {
                $output->writeLine(
                    sprintf('Preparing cache directory: %s', $cacheDir),
                    'text',
                    true
                );
                $this->cacheManager->getCache($cacheDir);
            }
            $output->writeLine(
                'Clearing cache directories',
                'text',
                true
            );
            $this->cacheManager->getCacheRegistry()->clearAllCaches();
        } elseif (is_string($caches) || is_array($caches)) {
            $output->writeLine(
                sprintf(
                    'Clearing caches: %s',
                    implode(', ', (array) $caches)
                )
            );

            foreach ((array) $caches as $cache) {
                $output->writeLine(
                    sprintf('Clearing cache directory: %s', $cache),
                    'text',
                    true
                );
                $this->cacheManager->getCache($cache)->clear();
            }
        }

        $output->writeLine(
            'Resetting registered caches.',
            'text',
            true
        );

        $this->cacheManager->resetRegisteredCaches();
        $output->writeLine('Done.');
    }
}
