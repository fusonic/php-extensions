<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\Normalizer;

use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;

final class UuidEntityIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param mixed|UuidEntityId $data
     * @param array<mixed>       $context
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return $data->getValue()->toRfc4122();
    }

    /**
     * @param mixed|UuidEntityId $data
     * @param array<mixed>       $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UuidEntityId;
    }

    /**
     * @param array<mixed> $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?UuidEntityId
    {
        if (null === $data || '' === $data) {
            return null;
        }

        if (!Uuid::isValid($data)) {
            return null;
        }

        /** @var UuidEntityId $id */
        $id = new $type();

        return $id::fromString($data);
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_subclass_of($type, UuidEntityId::class);
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            UuidEntityId::class => true,
        ];
    }
}
