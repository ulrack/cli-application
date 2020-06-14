<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Tests\Command;

use PHPUnit\Framework\TestCase;
use GrizzIt\Cache\Common\CacheInterface;
use GrizzIt\Vfs\Common\FileSystemInterface;
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
        $cacheFileSystem = $this->createMock(FileSystemInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $subject = new CacheClearCommand($cacheManager);

        $input->expects(static::once())
            ->method('getParameter')
            ->with('cache')
            ->willReturn(null);

        $cacheManager->expects(static::once())
            ->method('getCacheFileSystem')
            ->willReturn($cacheFileSystem);

        $cacheFileSystem->expects(static::once())
            ->method('list')
            ->with('')
            ->willReturn(['foo', 'bar']);

        $cacheManager->expects(static::exactly(2))
            ->method('getCache')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturn($this->createMock(CacheInterface::class));

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

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testCommandOneEntry(): void
    {
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $subject = new CacheClearCommand($cacheManager);

        $input->expects(static::once())
            ->method('getParameter')
            ->with('cache')
            ->willReturn('foo');

        $cacheManager->expects(static::once())
            ->method('getCache')
            ->with('foo')
            ->willReturn($cache);

        $cache->expects(static::once())
            ->method('clear');

        $output->expects(static::exactly(2))
            ->method('writeLine');

        $cacheManager->expects(static::once())
            ->method('resetRegisteredCaches');

        $subject->__invoke($input, $output);
    }

    /**
     * @return void
     *
     * @covers ::__construct
     * @covers ::__invoke
     */
    public function testCommandMultiple(): void
    {
        $cacheManager = $this->createMock(CacheManagerInterface::class);
        $cacheOne = $this->createMock(CacheInterface::class);
        $cacheTwo = $this->createMock(CacheInterface::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $subject = new CacheClearCommand($cacheManager);

        $input->expects(static::once())
            ->method('getParameter')
            ->with('cache')
            ->willReturn(['foo', 'bar']);

        $cacheManager->expects(static::exactly(2))
            ->method('getCache')
            ->withConsecutive(['foo'], ['bar'])
            ->willReturnOnConsecutiveCalls($cacheOne, $cacheTwo);

        $cacheOne->expects(static::once())
            ->method('clear');

        $cacheTwo->expects(static::once())
            ->method('clear');

        $output->expects(static::exactly(2))
            ->method('writeLine');

        $cacheManager->expects(static::once())
            ->method('resetRegisteredCaches');

        $subject->__invoke($input, $output);
    }
}
