<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Exception;

class ObjectTypeNotSupportedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Using object types in the url is not supported.');
    }
}
