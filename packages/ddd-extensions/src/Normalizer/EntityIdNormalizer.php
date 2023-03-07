<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Normalizer;

use Fusonic\DDDExtensions\Domain\Model\EntityId;
use Fusonic\DDDExtensions\Domain\Model\EntityIntegerId;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for {@link EntityId} value objects to serialize them into integers
 * and deserialize them into objects.
 */
class EntityIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param mixed|EntityIntegerId $object
     */
    public function normalize(mixed $object, string $format = null, array $context = []): int
    {
        return $object->getValue();
    }

    /**
     * @param mixed|EntityIntegerId $data
     */
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof EntityIntegerId;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): EntityIntegerId
    {
        /** @var EntityIntegerId $integerId */
        $integerId = new $type($data);

        return $integerId;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return \is_int($data) && is_a($type, EntityIntegerId::class, true);
    }
}
