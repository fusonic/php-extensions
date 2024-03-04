<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectDeserializationException;
use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectSerializationException;
use Fusonic\DDDExtensions\Doctrine\Types\ValueObjectType;
use Fusonic\DDDExtensions\Domain\Model\ValueObject;
use Fusonic\DDDExtensions\Tests\Doctrine\Types\AddressValueObjectType;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;
use Fusonic\DDDExtensions\Tests\Domain\User;

final class ValueObjectTypeTest extends AbstractTestCase
{
    public function testConversion(): void
    {
        $address1 = new AddressValueObject('Street', '1');

        $valueObjectType = new AddressValueObjectType();

        $databaseValue = $valueObjectType->convertToDatabaseValue($address1, $this->getDatabasePlatformStub());

        self::assertSame('{"number":"1","street":"Street"}', $databaseValue);

        $phpValue = $valueObjectType->convertToPHPValue($databaseValue, $this->getDatabasePlatformStub());

        self::assertInstanceOf(AddressValueObject::class, $phpValue);
        self::assertTrue($address1->equals($phpValue));
        self::assertSame('Street', $phpValue->getStreet());
        self::assertSame('1', $phpValue->getNumber());
    }

    public function testNonValueObjectToDatabaseConversion(): void
    {
        $user = new User('John');

        $valueObjectType = new AddressValueObjectType();

        $exception = null;
        try {
            $valueObjectType->convertToDatabaseValue($user, $this->getDatabasePlatformStub());
        } catch (\InvalidArgumentException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame(sprintf('Value should be of type `%s`.', ValueObject::class), $exception->getMessage());
    }

    public function testInvalidJsonToPhpConversion(): void
    {
        $valueObjectType = new AddressValueObjectType();

        $exception = null;
        try {
            $valueObjectType->convertToPHPValue('invalid json', $this->getDatabasePlatformStub());
        } catch (ValueObjectDeserializationException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
    }

    public function testInvalidTypeToPhpConversion(): void
    {
        $valueObjectType = new AddressValueObjectType();

        $exception = null;
        try {
            $valueObjectType->convertToPHPValue(1, $this->getDatabasePlatformStub());
        } catch (\InvalidArgumentException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame('Database value must be a string (json).', $exception->getMessage());
    }

    public function testNullValueConversion(): void
    {
        $valueObjectType = new AddressValueObjectType();

        $databaseValue = $valueObjectType->convertToDatabaseValue(null, $this->getDatabasePlatformStub());

        self::assertNull($databaseValue);

        $phpValue = $valueObjectType->convertToPHPValue($databaseValue, $this->getDatabasePlatformStub());

        self::assertNull($phpValue);
    }

    public function testSqlDeclaration(): void
    {
        $valueObjectType = new AddressValueObjectType();

        $platformStub = $this->getDatabasePlatformStub();
        $platformStub->method('getJsonTypeDeclarationSQL')
            ->willReturn('JSON');

        $fieldDeclarationStub =
            [
                'name' => 'address',
                'type' => AddressValueObjectType::class,
                'default' => null,
                'notnull' => true,
                'length' => null,
                'precision' => 10,
                'scale' => 0,
                'fixed' => false,
                'unsigned' => false,
                'autoincrement' => false,
                'columnDefinition' => null,
                'comment' => null,
                'version' => false,
            ];

        $sqlDeclaration = $valueObjectType->getSQLDeclaration($fieldDeclarationStub, $platformStub);

        self::assertSame('JSON', $sqlDeclaration);
    }

    public function testInvalidJsonDeserialization(): void
    {
        $exception = null;
        try {
            ValueObjectType::deserialize('{]}', static fn (array $data) => 'test');
        } catch (ValueObjectDeserializationException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ValueObjectDeserializationException::class, $exception);
        self::assertSame('State mismatch (invalid or malformed JSON)', $exception->getMessage());
    }

    public function testInvalidJsonArrayDeserialization(): void
    {
        $exception = null;
        try {
            ValueObjectType::deserialize('[{]}]', static fn (array $data) => 'test');
        } catch (ValueObjectDeserializationException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ValueObjectDeserializationException::class, $exception);
        self::assertSame('State mismatch (invalid or malformed JSON)', $exception->getMessage());
    }

    public function testInvalidJsonSerialization(): void
    {
        $address1 = new AddressValueObject('Street', '1');

        $exception = null;
        try {
            // Return a 'resource' array to trigger a json decoding exception
            // @phpstan-ignore-next-line
            ValueObjectType::serialize($address1, static fn (AddressValueObject $object) => get_resources());
        } catch (ValueObjectSerializationException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ValueObjectSerializationException::class, $exception);
        self::assertSame('Type is not supported', $exception->getMessage());
    }

    public function testInvalidJsonArraySerialization(): void
    {
        $address1 = new AddressValueObject('Street', '1');

        $exception = null;
        try {
            // Return a 'resource' array to trigger a json decoding exception
            // @phpstan-ignore-next-line
            ValueObjectType::serializeArray([$address1], static fn (AddressValueObject $object) => get_resources());
        } catch (ValueObjectSerializationException $e) {
            $exception = $e;
        }

        self::assertInstanceOf(ValueObjectSerializationException::class, $exception);
        self::assertSame('Type is not supported', $exception->getMessage());
    }
}
