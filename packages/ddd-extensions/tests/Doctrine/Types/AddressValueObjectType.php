<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Fusonic\DDDExtensions\Doctrine\Types\ValueObjectType;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;

/**
 * @extends ValueObjectType<AddressValueObject>
 */
final class AddressValueObjectType extends ValueObjectType
{
    public function getName(): string
    {
        return 'addressValueObject';
    }

    /**
     * @throws \JsonException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return self::serialize($value, static fn (AddressValueObject $object) => self::fromObject($object));
    }

    /**
     * @throws \JsonException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return self::deserialize($value, static fn (array $data) => self::toObject($data));
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function toObject(array $data): AddressValueObject
    {
        return new AddressValueObject($data['street'], $data['number']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromObject(AddressValueObject $object): array
    {
        return [
            'number' => $object->getNumber(),
            'street' => $object->getStreet(),
        ];
    }
}
