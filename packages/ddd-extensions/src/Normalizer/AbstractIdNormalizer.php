<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Normalizer;

use Fusonic\DDDExtensions\Domain\Model\AbstractId;
use Fusonic\DDDExtensions\Domain\Model\AbstractIntegerId;
use ReflectionHelper;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for AbstractId value objects to serialize them into strings and deserialize them into objects.
 */
class AbstractIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param AbstractId $object
     */
    public function normalize($object, string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof AbstractId;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): AbstractIntegerId
    {
        return new $type($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return ReflectionHelper::isInstanceOf($type, AbstractIntegerId::class);
    }
}
