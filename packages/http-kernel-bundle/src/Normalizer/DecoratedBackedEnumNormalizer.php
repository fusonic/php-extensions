<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Normalizer;

use Fusonic\HttpKernelBundle\Exception\InvalidEnumException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Decorate the original BackedEnumNormalizer to be able to provide a better error message.
 */
final readonly class DecoratedBackedEnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private BackedEnumNormalizer $inner,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->inner->getSupportedTypes($format);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): int|string
    {
        return $this->inner->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->inner->supportsNormalization($data, $format, $context);
    }

    /**
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        try {
            return $this->inner->denormalize($data, $type, $format, $context);
            // @phpstan-ignore catch.neverThrown (Ignore since the normalizer doesn't have the correct @throws tag)
        } catch (InvalidArgumentException) {
            throw new InvalidEnumException($type, $data, $context['deserialization_path'] ?? null);
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->inner->supportsDenormalization($data, $type, $format, $context);
    }
}
