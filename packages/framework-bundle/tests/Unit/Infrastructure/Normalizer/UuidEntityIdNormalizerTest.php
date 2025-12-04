<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Infrastructure\Normalizer;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Fusonic\FrameworkBundle\Infrastructure\Normalizer\UuidEntityIdNormalizer;
use PHPUnit\Framework\TestCase;

final class UuidEntityIdNormalizerTest extends TestCase
{
    private const DUMMY_UUID = '0195820a-af6e-7ec9-882e-53580038dd78';

    private const INVALID_UUID = 'abcdefg';

    public function testNormalize(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};
        $uuidEntityId = $uuidEntityIdClass::fromString(self::DUMMY_UUID);

        $normalizer = new UuidEntityIdNormalizer();

        // act
        $normalizedResult = $normalizer->normalize($uuidEntityId);

        // assert
        self::assertSame(self::DUMMY_UUID, $normalizedResult);
    }

    public function testSupportsNormalization(): void
    {
        // arrange
        $supportedClass = new readonly class extends UuidEntityId {};
        $unsupportedClass = new \stdClass();

        $normalizer = new UuidEntityIdNormalizer();

        // act + assert
        self::assertTrue($normalizer->supportsNormalization($supportedClass));
        self::assertFalse($normalizer->supportsNormalization($unsupportedClass));
    }

    public function testDenormalize(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};

        $normalizer = new UuidEntityIdNormalizer();

        // act
        $denormalizedResult = $normalizer->denormalize(self::DUMMY_UUID, $uuidEntityIdClass::class);

        // assert
        self::assertNotNull($denormalizedResult); // @phpstan-ignore staticMethod.alreadyNarrowedType (result might be null)
        self::assertSame(self::DUMMY_UUID, $denormalizedResult->getValue()->toRfc4122());
    }

    /**
     * An invalid Uuid should return null from the normalizer.
     */
    public function testInvalidUuidDenormalization(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};

        $normalizer = new UuidEntityIdNormalizer();

        // act
        $invalidUuid = $normalizer->denormalize(self::INVALID_UUID, $uuidEntityIdClass::class);

        // assert
        self::assertNull($invalidUuid); // @phpstan-ignore staticMethod.impossibleType (result might not be null)
    }

    public function testSupportsDenormalization(): void
    {
        // arrange
        $uuidEntityIdClass = new readonly class extends UuidEntityId {};

        $normalizer = new UuidEntityIdNormalizer();

        // act + assert
        self::assertTrue($normalizer->supportsDenormalization(self::DUMMY_UUID, $uuidEntityIdClass::class));
        self::assertTrue($normalizer->supportsDenormalization(null, $uuidEntityIdClass::class));
        self::assertTrue($normalizer->supportsDenormalization(self::INVALID_UUID, $uuidEntityIdClass::class));
        self::assertFalse($normalizer->supportsDenormalization(self::DUMMY_UUID, \stdClass::class));
    }

    public function testGetSupportedTypes(): void
    {
        // arrange
        $normalizer = new UuidEntityIdNormalizer();

        // act
        $supportedTypes = $normalizer->getSupportedTypes(format: null);

        // assert
        self::assertSame([UuidEntityId::class => true], $supportedTypes);
    }
}
