<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Normalizer;

use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * A normalizer for {@see ConstraintViolationException}.
 *
 * It uses the Symfony ConstraintViolationListNormalizer {@see ConstraintViolationListNormalizer} and enhances it with
 * an error name and the message template.
 */
final readonly class ConstraintViolationExceptionNormalizer implements NormalizerInterface
{
    public function __construct(
        private NormalizerInterface $normalizer
    ) {
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var ConstraintViolationException $exception */
        $exception = $object;
        $constraintViolationList = $exception->getConstraintViolationList();
        /** @var array<string, mixed> $normalized */
        $normalized = $this->normalizer->normalize($constraintViolationList, $format, $context);

        if (!isset($normalized['violations'])) {
            return $normalized;
        }

        foreach ($normalized['violations'] as $index => $normalizedViolation) {
            /** @var ConstraintViolation $violation */
            foreach ($constraintViolationList as $violation) {
                if (
                    isset($normalizedViolation['title'])
                    && $violation->getMessage() === $normalizedViolation['title']
                ) {
                    $constraint = $violation->getConstraint();
                    $code = $violation->getCode();

                    if (null !== $constraint && null !== $code) {
                        /** @var class-string<Constraint> $constraintClass */
                        $constraintClass = $constraint::class;
                        $normalized['violations'][$index]['errorName'] = $constraintClass::getErrorName($code);
                    }
                }
            }
        }

        return $normalized;
    }

    /**
     * @param array<mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ConstraintViolationException
            && $this->normalizer->supportsNormalization($data->getConstraintViolationList(), $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            ConstraintViolationException::class => true,
        ];
    }
}
