<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\Assert;

use Assert\InvalidArgumentException;
use Assert\LazyAssertionException;

class AssertionFailedException extends LazyAssertionException
{
    /**
     * @param InvalidArgumentException[] $errors
     */
    public function __construct(string $message, array $errors)
    {
        parent::__construct($message, $errors);
    }
}
