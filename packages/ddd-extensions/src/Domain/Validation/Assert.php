<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Domain\Validation;

use Assert\Assert as BaseAssert;
use Fusonic\DDDExtensions\Domain\Exception\DomainAssertionFailedException;

/**
 * Assertions that should be used in the domain context.
 */
class Assert
{
    public static function that(object|string $rootPath, string $propertyPath, mixed $value, ?string $defaultMessage = null): AssertionChain
    {
        $rootPropertyPath = \is_string($rootPath) ? $rootPath : self::getClassBasename($rootPath::class);
        $assertionChain = new AssertionChain($value, $rootPropertyPath, $propertyPath, $defaultMessage);

        return $assertionChain->setAssertionClassName(Assertion::class);
    }

    public static function lazy(object|string $rootPath): LazyAssertion
    {
        $rootPropertyPath = \is_string($rootPath) ? $rootPath : self::getClassBasename($rootPath::class);
        $lazyAssertion = new LazyAssertion($rootPropertyPath);

        return $lazyAssertion
            ->setAssertClass(BaseAssert::class)
            ->setExceptionClass(DomainAssertionFailedException::class);
    }

    protected static function getClassBasename(string $className): string
    {
        return substr((string) strrchr($className, '\\'), 1);
    }
}
