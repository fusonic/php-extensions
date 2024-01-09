<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\ConstraintViolation;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Wraps an {@see InvalidArgumentException} into a {@see ConstraintViolation}.
 */
class InvalidEnumConstraintViolation extends ConstraintViolation
{
    /**
     * @param class-string $enumClass
     *
     * @throws \ReflectionException
     */
    public function __construct(string $enumClass, mixed $data, ?string $propertyPath)
    {
        $reflectionEnum = new \ReflectionEnum($enumClass);

        $choices = array_map(static fn (\ReflectionEnumUnitCase $case) => $case->getName(), $reflectionEnum->getCases());
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
