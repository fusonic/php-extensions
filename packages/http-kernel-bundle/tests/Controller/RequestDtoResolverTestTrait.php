<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Tests\Controller;

use Fusonic\HttpKernelBundle\Controller\RequestDtoResolver;
use Fusonic\HttpKernelBundle\Normalizer\ConstraintViolationExceptionNormalizer;
use Fusonic\HttpKernelBundle\Normalizer\DecoratedBackedEnumNormalizer;
use Fusonic\HttpKernelBundle\Request\StrictRequestDataCollector;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait RequestDtoResolverTestTrait
{
    private function getRequestDtoResolver(): RequestDtoResolver
    {
        return new RequestDtoResolver($this->getDenormalizer(), $this->getValidator(), requestDataCollector: new StrictRequestDataCollector(strictRouteParams: true, strictQueryParams: true));
    }

    private function getDenormalizer(): DenormalizerInterface
    {
        $extractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $encoders = [new JsonEncoder()];
        $constraintViolationListNormalizer = new ConstraintViolationListNormalizer();
        $normalizers = [
            new UnwrappingDenormalizer(),
            new ConstraintViolationExceptionNormalizer($constraintViolationListNormalizer),
            new DecoratedBackedEnumNormalizer(new BackedEnumNormalizer()),
            new ProblemNormalizer(),
            new UidNormalizer(),
            new JsonSerializableNormalizer(),
            $constraintViolationListNormalizer,
            new DateTimeZoneNormalizer(),
            new DateTimeNormalizer(),
            new DateIntervalNormalizer(),
            new DataUriNormalizer(),
            new BackedEnumNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer(null, null, null, $extractor),
        ];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * @param array<mixed> $arguments
     */
    private function createArgumentMetadata(string $class, array $arguments): ArgumentMetadata
    {
        return new ArgumentMetadata('dto', $class, false, false, null, false, $arguments);
    }

    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->getValidator();
    }
}
