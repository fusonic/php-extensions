<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Faker;

abstract class DevelopmentFixture extends Fixture implements FixtureGroupInterface
{
    final public const FIXTURE_GROUP = 'development';

    protected Faker\Generator $faker;

    public function __construct(protected ?int $seed = null, protected ?string $locale = null)
    {
    }

    protected function getFaker(): Faker\Generator
    {
        if (isset($this->faker)) {
            return $this->faker;
        }

        $this->faker = Faker\Factory::create(locale: $this->locale ?? Faker\Factory::DEFAULT_LOCALE);

        if (null !== $this->seed) {
            // We use crc32 to generate an integer seed unique to each fixture class, ensuring the same class always
            // gets the same seed.
            $this->faker->seed($this->seed + crc32(static::class));
        }

        return $this->faker;
    }

    public static function getGroups(): array
    {
        return [
            self::FIXTURE_GROUP,
            static::class,
        ];
    }
}
