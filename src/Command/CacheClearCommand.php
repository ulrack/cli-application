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
        $output->writeLine('Clearing caches.');
        $this->cacheManager->getCacheRegistry()->clearAllCaches();
        $this->cacheManager->resetRegisteredCaches();
        $output->writeLine('Done.');
    }
}
