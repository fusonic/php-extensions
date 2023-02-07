<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\AssertExtensions\Validation;

use Assert\Assertion as BaseAssertion;
use Fusonic\AssertExtensions\Exception\AssertionFailedException;

class Assertion extends BaseAssertion
{
    /**
     * {@inheritDoc}
     */
    protected static function createException(mixed $value, mixed $message, mixed $code, mixed $propertyPath = null, array $constraints = []): void
    {
        $exception = parent::createException(
            $value,
            $message,
            $code,
            $propertyPath,
            $constraints
        );

        throw AssertionFailedException::fromErrors([$exception]);
    }
}
