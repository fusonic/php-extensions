<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Tests\Unit\Infrastructure\Normalizer;

use Fusonic\FrameworkBundle\Application\Message\Response\CollectionResponseInterface;
use Fusonic\FrameworkBundle\Infrastructure\Normalizer\CollectionResponseNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

final class CollectionResponseNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        // arrange
        $collectionItem = new class {
            public string $name = 'foo';
            public int $value = 42;
        };

        $collectionResponse = new readonly class($collectionItem) implements CollectionResponseInterface {
            public function __construct(
                private object $item,
            ) {
            }

            public function getValues(): array
            {
                return [$this->item];
            }
        };

        $normalizer = new CollectionResponseNormalizer(new ObjectNormalizer());

        // act
        $normalizedResult = $normalizer->normalize($collectionResponse);

        // assert
        self::assertSame([['name' => 'foo', 'value' => 42]], $normalizedResult);
    }

    public function testSupportsNormalization(): void
    {
        // arrange
        $supportedClass = new readonly class implements CollectionResponseInterface {
            public function getValues(): array
            {
                return [];
            }
        };
        $unsupportedClass = new \stdClass();

        $normalizer = new CollectionResponseNormalizer(self::createStub(NormalizerInterface::class));

        // act + assert
        self::assertTrue($normalizer->supportsNormalization($supportedClass));
        self::assertFalse($normalizer->supportsNormalization($unsupportedClass));
    }

    public function testGetSupportedTypes(): void
    {
        // arrange
        $normalizer = new CollectionResponseNormalizer(self::createStub(NormalizerInterface::class));

        // act
        $supportedTypes = $normalizer->getSupportedTypes(format: null);

        // assert
        self::assertSame([CollectionResponseInterface::class => true], $supportedTypes);
    }
}
