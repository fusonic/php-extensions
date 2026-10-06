<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Cache;

final class ReflectionClassCache
{
    /**
     * @var array<class-string, \ReflectionClass<object>>
     */
    private static array $reflectionClassCache = [];

    /**
     * @param class-string $className
     *
     * @return \ReflectionClass<object>
     */
    public static function getReflectionClass(string $className): \ReflectionClass
    {
        return self::$reflectionClassCache[$className] ??= new \ReflectionClass($className);
    }
}
