<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\Assert;

use Assert\Assert as BaseAssert;

class Assert
{
    /**
     * This methods executes the assertion directly and throws an exception if the assertion fails.
     */
    public static function that(object|string $rootPath, string $propertyPath, mixed $value, ?string $defaultMessage = null): AssertionChain
    {
        $rootPropertyPath = \is_string($rootPath) ? $rootPath : self::getClassBasename($rootPath::class);
        $assertionChain = new AssertionChain($value, $rootPropertyPath, $propertyPath, $defaultMessage);

        return $assertionChain->setAssertionClassName(self::getAssertionClass());
    }

    /**
     * This method will execute the assertions when the {@see LazyAssertion::verifyNow()} method is called.
     * This allows chaining of assertions.
     */
    public static function lazy(object|string $rootPath): LazyAssertion
    {
        $rootPropertyPath = \is_string($rootPath) ? $rootPath : self::getClassBasename($rootPath::class);
        $lazyAssertion = new LazyAssertion($rootPropertyPath);

        return $lazyAssertion
            ->setAssertClass(BaseAssert::class)
            ->setExceptionClass(self::getAssertionClass()::getExceptionClass());
    }

    /**
     * @return class-string<Assertion>
     */
    private static function getAssertionClass(): string
    {
        return Assertion::class;
    }

    private static function getClassBasename(string $className): string
    {
        $basename = strrchr($className, '\\');

        return substr(\is_string($basename) ? $basename : $className, 1);
    }
}
