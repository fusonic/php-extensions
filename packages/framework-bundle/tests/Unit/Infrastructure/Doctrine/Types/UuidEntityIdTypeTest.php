<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Unit\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Fusonic\FrameworkBundle\Domain\Id\UuidEntityId;
use Fusonic\FrameworkBundle\Infrastructure\Doctrine\Types\UuidEntityIdType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UuidEntityIdTypeTest extends TestCase
{
    private const DUMMY_UUID = '0195820d-38da-7972-adbe-474a038c4a66';

    /**
     * Simulated class extending the abstract {@see UuidEntityId} class.
     */
    private UuidEntityId $uuidEntityId;

    /**
     * Simulated type class extending the abstract {@see UuidEntityIdType} class.
     */
    private UuidEntityIdType $type;

    protected function setUp(): void
    {
        $this->uuidEntityId = new readonly class extends UuidEntityId {};

        $this->type = new class extends UuidEntityIdType {
            public const NAME = 'sample_uuid';

            /**
             * @var class-string<UuidEntityId>
             */
            private static string $uuidEntityIdClass;

            protected function getUuidEntityIdClass(): string
            {
                return self::$uuidEntityIdClass;
            }

            protected function getDoctrineTypeName(): string
            {
                return self::NAME;
            }

            /**
             * @param class-string<UuidEntityId> $uuidEntityIdClass
             */
            public static function setUuidEntityIdClass(string $uuidEntityIdClass): void
            {
                self::$uuidEntityIdClass = $uuidEntityIdClass;
            }
        };
        $this->type::setUuidEntityIdClass($this->uuidEntityId::class);
    }

    public function testUuidEntityIdConvertsToDatabaseValue(): void
    {
        // arrange
        $uuidEntityId = $this->uuidEntityId::fromString(self::DUMMY_UUID);

        // act
        $convertedDatabaseValue = $this->type->convertToDatabaseValue($uuidEntityId, new PostgreSQLPlatform());

        // assert
        self::assertSame(self::DUMMY_UUID, $convertedDatabaseValue);
    }

    public function testNullConvertsToDatabaseValue(): void
    {
        // act
        $convertedDatabaseValue = $this->type->convertToDatabaseValue(null, new PostgreSQLPlatform());

        // assert
        self::assertNull($convertedDatabaseValue);
    }

    public function testNotSupportedTypeDatabaseConversionThrowsException(): void
    {
        // assert
        if (class_exists(InvalidType::class)) { // Compatibility layer for doctrine/dbal 3.x
            $this->expectException(InvalidType::class);
            $this->expectExceptionMessage('Could not convert PHP value of type stdClass to type sample_uuid. Expected one of the following types: null, string, Fusonic\FrameworkBundle\Domain\Id\UuidEntityId.');
        } else {
            $this->expectException(ConversionException::class);
            $this->expectExceptionMessage('Could not convert PHP value of type stdClass to type sample_uuid. Expected one of the following types: null, string, Fusonic\FrameworkBundle\Domain\Id\UuidEntityId');
        }

        // act
        $this->type->convertToDatabaseValue(new \stdClass(), new PostgreSQLPlatform());
    }

    public function testUuidStringConvertsToDatabaseValue(): void
    {
        // act
        $convertedDatabaseValue = $this->type->convertToDatabaseValue(self::DUMMY_UUID, new PostgreSQLPlatform());

        // assert
        self::assertSame(self::DUMMY_UUID, $convertedDatabaseValue);
    }

    public function testInvalidUuidStringDatabaseConversionThrowsException(): void
    {
        // arrange
        $invalidUuidString = 'abcdefg';

        // assert
        if (class_exists(ValueNotConvertible::class)) { // Compatibility layer for doctrine/dbal 3.x
            $this->expectException(ValueNotConvertible::class);
            $this->expectExceptionMessage(
                \sprintf(
                    'Could not convert database value "%s" to Doctrine Type "sample_uuid".',
                    $invalidUuidString
                )
            );
        } else {
            $this->expectException(ConversionException::class);
            $this->expectExceptionMessage(
                \sprintf(
                    'Could not convert database value "%s" to Doctrine Type sample_uuid',
                    $invalidUuidString
                )
            );
        }

        // act
        $this->type->convertToDatabaseValue($invalidUuidString, new PostgreSQLPlatform());
    }

    public function testUuidEntityIdConvertsToPhpValue(): void
    {
        // arrange
        $uuidEntityId = new $this->uuidEntityId();

        // act
        $convertedPHPValue = $this->type->convertToPHPValue($uuidEntityId, new PostgreSQLPlatform());

        // assert
        self::assertNotNull($convertedPHPValue);
        self::assertTrue($uuidEntityId->equals($convertedPHPValue));
    }

    public function testNullConvertsToPhpValue(): void
    {
        // act
        $convertedPHPValue = $this->type->convertToPHPValue(null, self::getSqlitePlatform());

        // assert
        self::assertNull($convertedPHPValue);
    }

    public function testUuidStringConvertsToPhpValue(): void
    {
        // act
        $convertedPHPValue = $this->type->convertToPHPValue(self::DUMMY_UUID, self::getSqlitePlatform());

        // assert
        self::assertInstanceOf(UuidEntityId::class, $convertedPHPValue);
        self::assertSame(self::DUMMY_UUID, $convertedPHPValue->__toString());
    }

    public function testBinaryUuidStringConvertsToPhpValue(): void
    {
        // arrange
        $binaryUuidString = Uuid::fromString(self::DUMMY_UUID)->toBinary();

        // act
        $convertedPHPValue = $this->type->convertToPHPValue($binaryUuidString, self::getSqlitePlatform());

        // assert
        self::assertInstanceOf(UuidEntityId::class, $convertedPHPValue);
        self::assertSame(self::DUMMY_UUID, $convertedPHPValue->__toString());
    }

    public function testNotSupportedTypePhpConversionThrowsException(): void
    {
        // arrange
        $unsupportedTypeValue = 123456;

        // assert
        if (class_exists(InvalidType::class)) { // Compatibility layer for doctrine/dbal 3.x
            $this->expectException(InvalidType::class);
            $this->expectExceptionMessage(\sprintf(
                'Could not convert PHP value %d to type sample_uuid. Expected one of the following types: null, string, Fusonic\FrameworkBundle\Domain\Id\UuidEntityId.',
                $unsupportedTypeValue
            ));
        } else {
            $this->expectException(ConversionException::class);
            $this->expectExceptionMessage(\sprintf(
                'Could not convert PHP value %d to type sample_uuid. Expected one of the following types: null, string, Fusonic\FrameworkBundle\Domain\Id\UuidEntityId',
                $unsupportedTypeValue
            ));
        }

        // act
        $this->type->convertToPHPValue($unsupportedTypeValue, self::getSqlitePlatform());
    }

    public function testInvalidUuidStringPhpConversionThrowsException(): void
    {
        // arrange
        $invalidUuidString = 'abcdefg';

        // assert
        if (class_exists(ValueNotConvertible::class)) { // Compatibility layer for doctrine/dbal 3.x
            $this->expectException(ValueNotConvertible::class);
            $this->expectExceptionMessage(
                \sprintf(
                    'Could not convert database value "%s" to Doctrine Type "sample_uuid".',
                    $invalidUuidString
                )
            );
        } else {
            $this->expectException(ConversionException::class);
            $this->expectExceptionMessage(
                \sprintf(
                    'Could not convert database value "%s" to Doctrine Type sample_uuid',
                    $invalidUuidString
                )
            );
        }

        // act
        $this->type->convertToPHPValue($invalidUuidString, self::getSqlitePlatform());
    }

    #[DataProvider('provideSqlDeclarations')]
    public function testGetSqlDeclaration(AbstractPlatform $platform, string $expectedDeclaration): void
    {
        // act
        $sqlDeclaration = $this->type->getSQLDeclaration([], $platform);

        // assert
        self::assertSame($expectedDeclaration, $sqlDeclaration);
    }

    public static function provideSqlDeclarations(): \Generator
    {
        yield [new PostgreSQLPlatform(), 'UUID'];

        yield [self::getSqlitePlatform(), 'CHAR(36)'];

        yield [new MySQLPlatform(), 'CHAR(36)'];

        yield [new MariaDBPlatform(), 'CHAR(36)'];
    }

    /**
     * Compatibility layer for doctrine/dbal 3.x.
     */
    private static function getSqlitePlatform(): AbstractPlatform
    {
        // We cannot import the class at the top of the file as usual, as the different casing of 'SQL' causes problems
        // when autoloading.
        return class_exists(\Doctrine\DBAL\Platforms\SQLitePlatform::class)
            ? new \Doctrine\DBAL\Platforms\SQLitePlatform()
            : new \Doctrine\DBAL\Platforms\SqlitePlatform(); // @phpstan-ignore class.nameCase
    }
}
