<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\ConstraintViolation;

use Fusonic\HttpKernelBundle\Exception\InvalidEnumException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Wraps an {@see InvalidEnumException} into a {@see ConstraintViolation} to provide information about the valid choices.
 */
class InvalidEnumConstraintViolation extends ConstraintViolation
{
    /**
     * @param class-string<\UnitEnum> $enumClass
     *
     * @throws \ReflectionException
     */
    public function __construct(string $enumClass, mixed $data, ?string $propertyPath)
    {
        $reflectionEnum = new \ReflectionEnum($enumClass);

        $choices = array_map(static function (\ReflectionEnumUnitCase|\ReflectionEnumBackedCase $case) {
            if ($case instanceof \ReflectionEnumBackedCase) {
                return $case->getBackingValue();
            }

            return $case->getValue();
        }, $reflectionEnum->getCases());
        $constraint = new Choice(choices: $choices);

        parent::__construct(
            message: $constraint->message,
            messageTemplate: $constraint->message,
            parameters: ['{{ choices }}' => $choices, '{{ value }}' => $propertyPath],
            root: null,
            propertyPath: $propertyPath,
            invalidValue: $data,
            code: Choice::NO_SUCH_CHOICE_ERROR,
            constraint: $constraint
        );
    }
}
