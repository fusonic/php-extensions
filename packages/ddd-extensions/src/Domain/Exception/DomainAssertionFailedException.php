<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Exception;

use Assert\InvalidArgumentException;
use Assert\LazyAssertionException;

class DomainAssertionFailedException extends LazyAssertionException implements DomainExceptionInterface
{
    /**
     * @param InvalidArgumentException[] $errors
     */
    public function __construct(string $message, array $errors)
    {
        parent::__construct($message, $errors);
    }
}
