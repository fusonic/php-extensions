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
     * @param array<mixed>          $context
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): int
    {
        return $object->getValue();
    }

    /**
     * @param mixed|EntityIntegerId $data
     * @param array<mixed>          $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof EntityIntegerId;
    }

    /**
     * @param array<mixed> $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): EntityIntegerId
    {
        /** @var EntityIntegerId $integerId */
        $integerId = new $type($data);

        return $integerId;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_int($data) && is_a($type, EntityIntegerId::class, true);
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            EntityIntegerId::class => true,
        ];
    }
}
