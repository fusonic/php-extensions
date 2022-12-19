<?php

declare(strict_types=1);

namespace Fusonic\DDDExtensions\Tests;

use Fusonic\DDDExtensions\Doctrine\Exception\ValueObjectDeserializationException;
use Fusonic\DDDExtensions\Domain\Model\ValueObject;
use Fusonic\DDDExtensions\Tests\Doctrine\Types\AddressValueObjectType;
use Fusonic\DDDExtensions\Tests\Domain\AddressValueObject;
use Fusonic\DDDExtensions\Tests\Domain\User;

class ValueObjectTypeTest extends AbstractTestCase
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

    public function testInvalidJsonToPHPConversion(): void
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

    public function testInvalidTypeToPHPConversion(): void
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

    public function testSQLDeclaration(): void
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
}
