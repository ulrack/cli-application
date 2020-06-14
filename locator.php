<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

use Ulrack\CliApplication\Common\UlrackCliApplicationPackage;
use GrizzIt\Configuration\Component\Configuration\PackageLocator;

PackageLocator::registerLocation(
    __DIR__,
    UlrackCliApplicationPackage::PACKAGE_NAME
);
