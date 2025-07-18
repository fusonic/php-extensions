<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Infrastructure\DataFixtures;

use Fusonic\FrameworkBundle\Infrastructure\DataFixtures\ReferenceTrait;
use PHPUnit\Framework\TestCase;

final class ReferenceTraitTest extends TestCase
{
    public function testGetReference(): void
    {
        // arrange
        $classUsingRepository = new class {
            use ReferenceTrait;
        };

        // act
        $reference = $classUsingRepository::getReferenceName('id-1');

        // assert
        self::assertMatchesRegularExpression('/class@\S+\/ReferenceTraitTest\.php:.*_id-1/', $reference);
    }
}
