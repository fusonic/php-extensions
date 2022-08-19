<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Normalizer;

use Fusonic\DDDExtensions\Domain\Model\AbstractIntegerId;
use Fusonic\DDDExtensions\Reflection\ReflectionHelper;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for AbstractIntegerId value objects to serialize them into strings and deserialize them into objects.
 */
class AbstractIntegerIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param mixed|AbstractIntegerId $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    /**
     * @param mixed|AbstractIntegerId $data
     */
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof AbstractIntegerId;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): AbstractIntegerId
    {
        /** @var AbstractIntegerId $integerId */
        $integerId = new $type($data);

        return $integerId;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return is_int($data) && ReflectionHelper::isInstanceOf($type, AbstractIntegerId::class);
    }
}
