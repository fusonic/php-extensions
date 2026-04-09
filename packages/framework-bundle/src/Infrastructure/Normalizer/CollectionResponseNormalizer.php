<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\Normalizer;

use Fusonic\FrameworkBundle\Application\Message\Response\CollectionResponseInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CollectionResponseNormalizer implements NormalizerInterface
{
    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @param mixed|CollectionResponseInterface $data
     *
     * @return array<mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return array_map(
            callback: fn (mixed $value): mixed => $this->normalizer->normalize($value, $format, $context),
            array: $data->getValues(),
        );
    }

    /**
     * @param mixed|CollectionResponseInterface $data
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CollectionResponseInterface;
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            CollectionResponseInterface::class => true,
        ];
    }
}
