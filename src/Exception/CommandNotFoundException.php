<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Exception;

use Exception;

class CommandNotFoundException extends Exception
{
    /**
     * Constructor.
     *
     * @param string[] $command
     * @param int $index
     */
    public function __construct(array $command, int $index)
    {
        parent::__construct(
            sprintf(
                'Could not find command %s, at index #%d %s',
                implode(' ', $command),
                $index,
                $command[$index]
            ),
            127
        );
    }
}
