<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Exception;

use LogicException;

final class UnsupportedTypeException extends LogicException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
