<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\ErrorHandler;

use Fusonic\HttpKernelBundle\ConstraintViolation\ArgumentCountConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\InvalidEnumConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\MissingConstructorArgumentsConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\NotNormalizableValueConstraintViolation;
use Fusonic\HttpKernelBundle\ConstraintViolation\TypeConstraintViolation;
use Fusonic\HttpKernelBundle\Exception\ConstraintViolationException;
use Fusonic\HttpKernelBundle\Exception\InvalidEnumException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param array<string, mixed> $data
     * @param class-string         $className
     */
    public function handleDenormalizeError(\Throwable $ex, array $data, string $className): \Throwable
    {
        if ($ex instanceof InvalidEnumException) {
            return ConstraintViolationException::fromConstraintViolation(
                new InvalidEnumConstraintViolation($ex->enumClass, $ex->data, $ex->propertyPath)
            );
        }

        if ($ex instanceof NotNormalizableValueException) {
            return ConstraintViolationException::fromConstraintViolation(
                new NotNormalizableValueConstraintViolation($ex, $data, $className)
            );
        }

        if ($ex instanceof MissingConstructorArgumentsException) {
            return ConstraintViolationException::fromConstraintViolation(
                new MissingConstructorArgumentsConstraintViolation($ex)
            );
        }

        if ($ex instanceof \ArgumentCountError) {
            return ConstraintViolationException::fromConstraintViolation(new ArgumentCountConstraintViolation($ex));
        }

        if ($ex instanceof \TypeError) {
            return ConstraintViolationException::fromConstraintViolation(new TypeConstraintViolation($ex));
        }

        return $ex;
    }

    public function handleConstraintViolations(ConstraintViolationListInterface $list): void
    {
        throw new ConstraintViolationException($list);
    }
}
