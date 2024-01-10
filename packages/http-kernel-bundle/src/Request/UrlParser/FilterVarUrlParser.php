<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Request\UrlParser;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

final class FilterVarUrlParser implements UrlParserInterface
{
    public function isNull(string $value): bool
    {
        return '' === $value;
    }

    public function parseInteger(string $value): ?int
    {
        return filter_var($value, \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE);
    }

    public function parseFloat(string $value): ?float
    {
        return filter_var($value, \FILTER_VALIDATE_FLOAT, \FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param string|array<string> $value
     *
     * @return array<string>
     */
    public function handleArrayParameter(string|array $value): array
    {
        if (\is_string($value)) {
            return [$value];
        }

        return $value;
    }

    public function parseBoolean(string $value): ?bool
    {
        return filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
    }

    public function parseString(string $value): string
    {
        return $value;
    }

    public function handleFailure(string $attribute, string $className, string $expectedType, string $value, string $propertyPath): void
    {
        throw NotNormalizableValueException::createForUnexpectedDataType(sprintf('The type of the "%s" attribute for class "%s" must be %s ("%s" given).', $attribute, $className, $expectedType, $value), $value, [$expectedType], $propertyPath);
    }
}
