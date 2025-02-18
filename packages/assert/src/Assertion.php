<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\Assert;

use Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    /**
     * @param array<mixed> $constraints
     */
    protected static function createException(
        mixed $value,
        mixed $message,
        mixed $code,
        mixed $propertyPath = null,
        array $constraints = [],
    ): void {
        /** @var \Assert\InvalidArgumentException $exception */
        $exception = parent::createException(
            $value,
            $message,
            $code,
            $propertyPath,
            $constraints
        );

        throw self::getExceptionClass()::fromErrors([$exception]);
    }

    /**
     * @return class-string<AssertionFailedException>
     */
    public static function getExceptionClass(): string
    {
        return AssertionFailedException::class;
    }
}
