<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Validation;

use Assert\Assertion as BaseAssertion;
use Fusonic\DDDExtensions\Domain\Exception\DomainAssertionFailedException;

class Assertion extends BaseAssertion
{
    /**
     * {@inheritDoc}
     *
     * @param mixed   $propertyPath
     * @param mixed[] $constraints
     */
    protected static function createException($value, $message, $code, $propertyPath = null, array $constraints = []): void
    {
        $exception = parent::createException(
            $value,
            $message,
            $code,
            $propertyPath,
            $constraints
        );

        throw DomainAssertionFailedException::fromErrors([$exception]);
    }
}
