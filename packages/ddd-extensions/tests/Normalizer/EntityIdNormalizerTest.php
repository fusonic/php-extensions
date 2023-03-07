<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Normalizer;

use Fusonic\DDDExtensions\Normalizer\EntityIdNormalizer;
use Fusonic\DDDExtensions\Tests\AbstractTestCase;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;
use Fusonic\DDDExtensions\Tests\Domain\JobId;

final class EntityIdNormalizerTest extends AbstractTestCase
{
    public function testNormalize(): void
    {
        $normalizer = new EntityIdNormalizer();

        $value = 1;
        $supportedObject = new JobId($value);
        $unsupportedObject = new AddressValueObject('Street', '1');

        self::assertTrue($normalizer->supportsNormalization($supportedObject));
        self::assertFalse($normalizer->supportsNormalization($unsupportedObject));

        self::assertSame($value, $normalizer->normalize($supportedObject));
    }

    public function testDenormalizer(): void
    {
        $normalizer = new EntityIdNormalizer();

        $value = 1;

        self::assertTrue($normalizer->supportsDenormalization($value, JobId::class));
        self::assertFalse($normalizer->supportsDenormalization($value, AddressValueObject::class));

        $denormalized = $normalizer->denormalize($value, JobId::class);

        self::assertInstanceOf(JobId::class, $denormalized);
        self::assertSame(1, $denormalized->getValue());
    }
}
