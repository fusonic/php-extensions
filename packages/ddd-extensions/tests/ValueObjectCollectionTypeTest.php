<?php

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectDeserializationException;
use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectSerializationException;
use Fusonic\DDDExtensions\Domain\Model\ValueObject;
use Fusonic\DDDExtensions\Tests\Doctrine\Types\AddressValueObjectCollectionType;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;
use Fusonic\DDDExtensions\Tests\Domain\User;

class ValueObjectCollectionTypeTest extends AbstractTestCase
{
    public function testConversion(): void
    {
        $addresses = [
            new AddressValueObject('Street', '1'),
            new AddressValueObject('Lane', '2'),
        ];

        $valueObjectCollectionType = new AddressValueObjectCollectionType();

        $databaseValue = $valueObjectCollectionType->convertToDatabaseValue($addresses, $this->getDatabasePlatformStub());

        self::assertSame('[{"number":"1","street":"Street"},{"number":"2","street":"Lane"}]', $databaseValue);

        $phpValue = $valueObjectCollectionType->convertToPHPValue($databaseValue, $this->getDatabasePlatformStub());

        self::assertIsArray($phpValue);
        self::assertCount(2, $phpValue);
        self::assertInstanceOf(AddressValueObject::class, $phpValue[0]);
        self::assertInstanceOf(AddressValueObject::class, $phpValue[1]);
        self::assertSame('Street', $phpValue[0]->getStreet());
        self::assertSame('1', $phpValue[0]->getNumber());
        self::assertSame('Lane', $phpValue[1]->getStreet());
        self::assertSame('2', $phpValue[1]->getNumber());
    }

    public function testNonValueObjectToDatabaseConversion(): void
    {
        $user = new User('John');

        $valueObjectType = new AddressValueObjectCollectionType();

        $exception = null;
        try {
            $valueObjectType->convertToDatabaseValue([$user], $this->getDatabasePlatformStub());
        } catch (ValueObjectSerializationException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame(sprintf('Values in array should be of type `%s`.', ValueObject::class), $exception->getMessage());
    }

    public function testInvalidJsonToPHPConversion(): void
    {
        $valueObjectType = new AddressValueObjectCollectionType();

        $exception = null;
        try {
            $valueObjectType->convertToPHPValue('invalid json', $this->getDatabasePlatformStub());
        } catch (ValueObjectDeserializationException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
    }

    public function testInvalidTypeToPHPConversion(): void
    {
        $valueObjectType = new AddressValueObjectCollectionType();

        $exception = null;
        try {
            $valueObjectType->convertToPHPValue(1, $this->getDatabasePlatformStub());
        } catch (ValueObjectDeserializationException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame('Database value must be a string (json array).', $exception->getMessage());
    }

    public function testNullValueToDatabaseConversion(): void
    {
        $valueObjectType = new AddressValueObjectCollectionType();

        $exception = null;

        try {
            $valueObjectType->convertToDatabaseValue(null, $this->getDatabasePlatformStub());
        } catch (ValueObjectSerializationException $e) {
            $exception = $e;
        }

        self::assertNotNull($exception);
        self::assertSame(
            sprintf('Value must be an array of type `%s[]`', ValueObject::class),
            $exception->getMessage()
        );
    }

    public function testNullValueToPHPConversion(): void
    {
        $valueObjectType = new AddressValueObjectCollectionType();

        $phpValue = $valueObjectType->convertToPHPValue(null, $this->getDatabasePlatformStub());

        self::assertEquals([], $phpValue);

        $phpValue = $valueObjectType->convertToPHPValue('null', $this->getDatabasePlatformStub());

        self::assertEquals([], $phpValue);
    }

    public function testSQLDeclaration(): void
    {
        $valueObjectType = new AddressValueObjectCollectionType();

        $platformStub = $this->getDatabasePlatformStub();
        $platformStub->method('getJsonTypeDeclarationSQL')
            ->willReturn('JSON');

        $fieldDeclarationStub =
            [
                'name' => 'address',
                'type' => AddressValueObjectCollectionType::class,
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
}
