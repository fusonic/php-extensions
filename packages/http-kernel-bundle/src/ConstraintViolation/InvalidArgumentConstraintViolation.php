<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\ConstraintViolation;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Wraps an {@see InvalidArgumentException} into a {@see ConstraintViolation}.
 */
class InvalidArgumentConstraintViolation extends ConstraintViolation
{
    private const EXPECTED_MATCHES = 2;

    /**
     * @param array<string, mixed> $data
     * @param class-string         $className
     *
     * @throws \ReflectionException
     */
    public function __construct(InvalidArgumentException $error, array $data, string $className)
    {
        $message = $error->getMessage();

        if (!str_starts_with($message, 'The data must belong to a backed enumeration of type ')) {
            throw $error;
        }

        $matches = [];
        $pattern = '/The data must belong to a backed enumeration of type (.+)/';

        preg_match($pattern, $message, $matches);

        if (\count($matches) < self::EXPECTED_MATCHES) {
            throw $error;
        }

        /** @var class-string<\UnitEnum>|null $enumClassName */
        $enumClassName = $matches[1] ?? null;

        if (null === $enumClassName) {
            throw $error;
        }

        $reflectionEnum = new \ReflectionEnum($enumClassName);

        $choices = array_map(static fn (\ReflectionEnumUnitCase $case) => $case->getName(), $reflectionEnum->getCases());
        $constraint = new Choice(choices: $choices);
        $propertyPathAndValue = $this->determinePropertyPathAndValue($data, $className, $reflectionEnum);

        parent::__construct(
            message: $constraint->message,
            messageTemplate: $constraint->message,
            parameters: ['{{ choices }}' => $choices, '{{ value }}' => $propertyPathAndValue[1] ?? null],
            root: null,
            propertyPath: $propertyPathAndValue[0] ?? null,
            invalidValue: $propertyPathAndValue[1] ?? null,
            code: Choice::NO_SUCH_CHOICE_ERROR,
            constraint: $constraint
        );
    }

    /**
     * Find out which property and which value are invalid.
     *
     * @param array<string, mixed> $data
     * @param class-string         $className
     *
     * @return string[]
     */
    private function determinePropertyPathAndValue(array $data, string $className, \ReflectionEnum $reflectionEnum): array
    {
        $class = new \ReflectionClass($className);

        $constructor = $class->getConstructor();
        $parameters = $constructor?->getParameters() ?? [];

        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();
            /** @var \ReflectionNamedType|null $reflectionType */
            $reflectionType = $parameter->getType();
            $x = $reflectionType?->getName();
            $y = $reflectionEnum->getName();

            if (null !== $reflectionType && $reflectionEnum->getName() === $reflectionType->getName()) {
                $parameterValue = $data[$parameterName] ?? null;

                return [$parameterName, $parameterValue];
            }
        }

        return [];
    }
}
