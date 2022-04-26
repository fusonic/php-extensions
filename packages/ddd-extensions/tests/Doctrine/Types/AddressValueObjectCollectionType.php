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
class AddressValueObjectCollectionType extends ValueObjectType
{
    public function getName(): string
    {
        return 'addressValueObject_collection';
    }

    /**
     * @throws \JsonException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        return self::serializeArray($value, static fn (AddressValueObject $object) => AddressValueObjectType::fromObject($object));
    }

    /**
     * @throws \JsonException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return self::deserializeArray($value, static fn (array $data) => AddressValueObjectType::toObject($data));
    }
}
