<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Command;

use PHPUnit\Framework\TestCase;
use GrizzIt\Cache\Common\CacheRegistryInterface;
use Ulrack\Command\Common\Command\InputInterface;
use Ulrack\Command\Common\Command\OutputInterface;
use Ulrack\CliApplication\Command\CacheClearCommand;
use Ulrack\Kernel\Common\Manager\CacheManagerInterface;

/**
 * @coversDefaultClass \Ulrack\CliApplication\Command\CacheClearCommand
 */
class CacheClearCommandTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testCommand(): void
    {
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheRegistry = $this->createMock(CacheRegistryInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $subject = new CacheClearCommand($cacheManager);

        $output->expects(static::exactly(2))
            ->method('writeLine');

        $cacheManager->expects(static::once())
            ->method('getCacheRegistry')
            ->willReturn($cacheRegistry);

        $cacheRegistry->expects(static::once())
            ->method('clearAllCaches');

        $cacheManager->expects(static::once())
            ->method('resetRegisteredCaches');

        $subject->__invoke($input, $output);
    }
}
