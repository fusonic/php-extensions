<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\DataFixtures;

trait ReferenceTrait
{
    public static function getReferenceName(string|int $objectReference): string
    {
        return implode('_', [self::class, $objectReference]);
    }
}
