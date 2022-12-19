<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectDeserializationException;
use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectSerializationException;
use Fusonic\DDDExtensions\Domain\Model\ValueObject;

/**
 * @template T
 *
 * Base class for ValueObject database types.
 */
abstract class ValueObjectType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($column);
    }

    /**
     * @param callable(array<string, mixed>): ?T $convert
     */
    public static function deserialize(mixed $value, callable $convert): ?ValueObject
    {
        if (null === $value) {
            return null;
        }

        if (!\is_string($value)) {
            throw new ValueObjectDeserializationException('Database value must be a string (json).');
        }

        try {
            return $convert(json_decode($value, true, 512, \JSON_THROW_ON_ERROR));
        } catch (\JsonException $exception) {
            throw new ValueObjectDeserializationException($exception->getMessage(), $exception);
        }
    }

    /**
     * @param T|null                            $value
     * @param callable(T): array<string, mixed> $convert
     */
    public static function serialize(mixed $value, callable $convert): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ValueObject) {
            throw new ValueObjectSerializationException(sprintf('Value should be of type `%s`.', ValueObject::class));
        }

        try {
            return json_encode($convert($value), \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new ValueObjectSerializationException($exception->getMessage(), $exception);
        }
    }

    /**
     * @param callable(array<string, mixed>): T $convert
     *
     * @return T[]
     */
    public static function deserializeArray(mixed $value, callable $convert): array
    {
        if (null === $value) {
            return [];
        }

        if (!\is_string($value)) {
            throw new ValueObjectDeserializationException('Database value must be a string (json array).');
        }

        try {
            $data = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new ValueObjectDeserializationException($exception->getMessage(), $exception);
        }

        if (null === $data) {
            return [];
        }

        return array_map(static fn (array $item) => $convert($item), $data);
    }

    /**
     * @param T[]|null                          $value
     * @param callable(T): array<string, mixed> $convert
     */
    public static function serializeArray(mixed $value, callable $convert): string
    {
        if (!\is_array($value)) {
            throw new ValueObjectSerializationException(sprintf('Value must be an array of type `%s[]`', ValueObject::class));
        }

        try {
            return json_encode(
                array_map(
                    static function (mixed $valueObject) use ($convert) {
                        if (!$valueObject instanceof ValueObject) {
                            throw new ValueObjectSerializationException(sprintf('Values in array should be of type `%s`.', ValueObject::class));
                        }

                        return $convert($valueObject);
                    },
                    $value
                ),
                \JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $exception) {
            throw new ValueObjectSerializationException($exception->getMessage(), $exception);
        }
    }
}
