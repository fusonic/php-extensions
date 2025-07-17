<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;
use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;

abstract class UuidEntityIdType extends Type
{
    /**
     * @return class-string<UuidEntityId>
     */
    abstract protected function getUuidEntityIdClass(): string;

    abstract protected function getDoctrineTypeName(): string;

    /**
     * @deprecated Compatibility layer for doctrine/dbal 3.x
     */
    final public function getName(): string
    {
        return static::getDoctrineTypeName();
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof UuidEntityId) {
            return $value->getValue()->toRfc4122();
        }

        if (null === $value) {
            return null;
        }

        if (!\is_string($value)) {
            $this->throwInvalidType($value);
        }

        try {
            $uuidEntityId = $this->getUuidEntityIdClass()::fromString($value);

            return $uuidEntityId->getValue()->toRfc4122();
        } catch (\InvalidArgumentException $e) {
            $this->throwValueNotConvertible($value, $e);
        }
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?UuidEntityId
    {
        if ($value instanceof UuidEntityId || null === $value) {
            return $value;
        }

        $typeClass = $this->getUuidEntityIdClass();

        if (!\is_string($value)) {
            $this->throwInvalidType($value);
        }

        try {
            return $typeClass::fromString($value);
        } catch (\InvalidArgumentException $e) {
            $this->throwValueNotConvertible($value, $e);
        }
    }

    /**
     * @throws ConversionException|InvalidType
     */
    private function throwInvalidType(mixed $value): never
    {
        // Compatibility layer for doctrine/dbal 3.x
        if (!class_exists(InvalidType::class)) {
            /* @phpstan-ignore staticMethod.notFound */
            throw ConversionException::conversionFailedInvalidType(
                value: $value,
                toType: $this->getDoctrineTypeName(),
                possibleTypes: ['null', 'string', UuidEntityId::class]
            );
        }

        throw InvalidType::new(
            value: $value,
            toType: $this->getDoctrineTypeName(),
            possibleTypes: ['null', 'string', UuidEntityId::class]
        );
    }

    /**
     * @throws ConversionException|ValueNotConvertible
     */
    private function throwValueNotConvertible(mixed $value, \Throwable $previous): never
    {
        // Compatibility layer for doctrine/dbal 3.x
        if (!class_exists(ValueNotConvertible::class)) {
            /* @phpstan-ignore staticMethod.notFound */
            throw ConversionException::conversionFailed(
                value: $value,
                toType: $this->getDoctrineTypeName(),
                previous: $previous,
            );
        }

        throw ValueNotConvertible::new(
            value: $value,
            toType: $this->getDoctrineTypeName(),
            previous: $previous
        );
    }
}
