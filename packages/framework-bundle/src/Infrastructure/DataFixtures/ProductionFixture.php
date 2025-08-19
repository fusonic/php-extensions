<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

abstract class ProductionFixture extends Fixture implements FixtureGroupInterface
{
    final public const FIXTURE_GROUP = 'production';

    public static function getGroups(): array
    {
        return [
            self::FIXTURE_GROUP,
            static::class,
        ];
    }
}
