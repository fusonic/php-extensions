<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Types;

use Fusonic\HttpKernelBundle\Cache\ReflectionClassCache;

class TypeHelper
{
    public const ENUM_TYPE = 'enum';

    public static function isUnionType(string $type): bool
    {
        return str_starts_with($type, '(') && str_ends_with($type, ')');
    }

    public static function isTypeEnum(string $type): bool
    {
        return ReflectionClassCache::getReflectionClass($type)->isEnum();
    }
}
