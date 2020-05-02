<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

namespace Ulrack\CliApplication\Exception;

use Exception;

class UnpassedValidationException extends Exception
{
    /**
     * Constructor.
     *
     * @param string ...$errorMessages
     */
    public function __construct(string ...$errorMessages)
    {
        parent::__construct(
            sprintf(
                'Validation failed with the following message:' .
                PHP_EOL .
                ' %s',
                implode(PHP_EOL, $errorMessages)
            ),
            1
        );
    }
}
