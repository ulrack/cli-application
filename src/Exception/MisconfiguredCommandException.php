<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Exception;

use Exception;

class MisconfiguredCommandException extends Exception
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(
            'Command contains an incorrect configuration.'
        );
    }
}
