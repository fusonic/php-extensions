<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

final class ReflectionHelper
{
    /**
     * @param class-string $className
     * @param class-string $baseClass
     */
    public static function isInstanceOf(string $className, string $baseClass): bool
    {
        if (class_exists($className)) {
            $reflectionClass = new \ReflectionClass($className);

            return $reflectionClass->isSubclassOf($baseClass);
        }

        return false;
    }
}
